<?php

namespace App\Controller;

use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use App\Entity\SmsIn;
use App\Service\SendMessage;

class SmsInController extends Controller
{

    /**
     * @Route("/sms/receive", name="receive_sms")
     */
    public function receiveAction(Request $request, SendMessage $sendMessage)
    {
    	//access the get parameters, %p for sms_origin and %a for message
    	$raw_message = $request->query->get('message');
    	$sms_origin_raw = $request->query->get('phone_no');
    	$sms_origin = $this->cleanNumber($sms_origin_raw);

        // date time for when this message was received
        $now = new \DateTime("now");

        // split the message into parts and access the different variables
        $parts = $this->getMessageParts($raw_message);

        list($group_name, $message) = [$parts['group_name'], $parts['message'] ];

        $available_groups = $this->getAllGroupNames();
        if(in_array($group_name, $available_groups)){

        	// group name found
        	$group = $this->find("Grp", "One", "Title", $group_name);
        	$group_admins = $this->getAdminsFromGroup($group);

	        $group_contacts = $this->fetchAllContacts($group);

        	if(in_array($sms_origin, $group_admins)){
        		// sender is admin
	            // set values to a new SMSIn entity
	            $smsIn = new SMSIn();
	            $smsIn->setGroupName($group_name);
	            $smsIn->setSmsOrigin($sms_origin);
	            $smsIn->setMessage($message);
            	$smsIn->setReceivedOn($now);

	            // formulate reply
	            $reply = $this->makeReply($group_name);

	            // replace spaces in reply with + signs
	            $concatenated_reply = str_replace(" ", "+", $reply);

	            // save to table
	            $this->save($smsIn);

	            // send message to appropriate group
	            $concat_message = str_replace(" ", "+", $message);
	            $concat_message .= " Reply to ".$sms_origin;	            
        		$send_to_group = $sendMessage->sendMessage($group_contacts, $concat_message);

	        } else {
	        	//not an admin
	            // formulate reply
	            $reply = "Sorry, you are not an admin for the group: $group_name. Myle-Post bulk messaging system. To use service, call 0705285959";

	            // replace spaces in reply with + signs
	            $concatenated_reply = str_replace(" ", "+", $reply);
	        }

        } else {
        	// no group exists with that name
            // formulate reply
            // if is member of any group, mind the response
        	$contact = $this->find("Contact", "One", "Number", $sms_origin);
        	if($contact){
        		$grp = $contact->getGrp();
        		$grp_name = $grp->getTitle();
        		$admins = explode(",", $grp->getAdmins());
        		$first_admin = $admins[0];
        		$reply = "Contact $first_admin of $grp_name for more information. Myle-Post bulk messaging system. To use service, call 0705285959";
        	} else {
        		$reply = "No group exists with title: $group_name. Myle-Post bulk messaging system. To use service, call 0705285959";
        	}

            // replace spaces in reply with + signs
            $concatenated_reply = str_replace(" ", "+", $reply);
        }

        $send_response = $sendMessage->sendMessage($sms_origin, $concatenated_reply);
        $this->addFlash('success', "Message sent");

        return $this->render('sms_in/index.html.twig', ['message' => $message]);
	}
    /**
     * @Route("/sms/messages", name="list_messages")
     */
    public function listAction(Request $request, SendMessage $sendMessage)
    {
        // display all entries from the mpesa table.
        $data = [];
        $messages = $this->em()->getRepository('App:SMSIn')
            ->findAll();
        $data['messages'] = $messages;
        return $this->render('sms_in/messages.html.twig', $data);
    }

    /**
     * @Route("/sms/clear", name="clear_messages")
     */
    public function clearAction(Request $request, SendMessage $sendMessage)
    {
        // this is destructive! it will clear the database and the log file.
        $messages = $this->em()->getRepository('App:SMSIn')
            ->findAll();
        foreach($messages as $message){
            $this->em()->remove($message);
            $this->em()->flush();
        }
        $date = date('Y-m-d');
        $path_to_log = $this->container->get('kernel')->getLogDir();
        $mpesa_log = "sms-".$date.".log";

        file_put_contents($path_to_log."/".$mpesa_log, "");
        return $this->render('sms_in/delete.html.twig');
    }


    private function save($entity){
        $this->em()->persist($entity);
        $this->em()->flush();
    }

    private function makeReply($group_name){
        // response to admin
    	$response = "You have successfully sent the message to the group: $group_name";
        return $response;
    }

    private function em(){
        $em = $this->getDoctrine()->getManager();
        return $em;
    }

    private function getAllGroupNames(){
    	$groups = $this->em()->getRepository('App:Grp')
    		->findAll();
    	$groups_list = [];

    	foreach($groups as $group){
    		$groups_list[] = $group->getTitle();
    	}
    	return $groups_list;
    }

    private function getAdminsFromGroup($group){
    	$admins_list = [];
    	$admins = explode("," , $group->getAdmins());
    	foreach($admins as $admin){
    		$admins_list[] = $admin;
    	}
    	return $admins_list;
    }

    private function getMessageParts($message){
        // split message by spaces
        $splitted_message = explode(" ", $message);
        $parts = [];
        $group_name = array_shift($splitted_message);
        $message = implode(" ", $splitted_message);

        $parts['group_name'] = strtolower($group_name);
        $parts['message'] = $message;

        return $parts;
    }

    private function fetchAllContacts($group){
        $contacts_list = [];
        $contacts = $this->em()->getRepository('App:Contact')
            ->findByGrp($group);
        foreach($contacts as $contact){
            $contacts_list[] = $contact->getNumber();
        }
        $contacts_string = implode("+", $contacts_list);
        return $contacts_string;
    }

    private function allContacts(){
        $contacts_list = [];
        $contacts = $this->em()->getRepository('App:Contact')
            ->findAll();
        foreach($contacts as $contact){
            $contacts_list[] = $contact->getNumber();
        }
        return $contacts_list;
    }

    private function cleanNumber($sms_origin_raw){
    	$number = "";
        if(strlen($sms_origin_raw) > 10){
            $number = str_replace("+254", "0", $sms_origin_raw);
        } else {
            $number = $sms_origin_raw;
        }
        return $number;
    }

    private function find($entity, $qty, $by, $value){
        if($qty == "All"){
            $query_string = "findAll";
        } else {
            $query_string = "find".$qty."By".$by;
        }

        $entity = $this->em()->getRepository("App:$entity")->$query_string($value);
        return $entity;
    }

}
