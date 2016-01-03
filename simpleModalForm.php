<?php

/*
 * SimpleModal Contact Form
 * http://www.ericmmartin.com/projects/simplemodal/
 * http://code.google.com/p/simplemodal/
 *
 * Copyright (c) 2009 Eric Martin - http://ericmmartin.com
 *
 * Licensed under the MIT license:
 *   http://www.opensource.org/licenses/mit-license.php
 *
 * Revision: $Id: contact-dist.php 254 2010-07-23 05:14:44Z emartin24 $
 *
 */

// User settings
$to = "mail@artur.work";
$subject = '';

// Include extra form fields and/or submitter data?
// false = do not include
$extra = array(
	"form_subject"	=> true,
	"form_cc"	=> true,
	"ip"		=> true,
	"user_agent"	=> true
);

// Process
$action = isset($_POST["action"]) ? $_POST["action"] : "";
if (empty($action)) {
	// Send back the contact form HTML
	$output = "<div style='display:none'>
	<div class='contact-top'></div>
	<div class='contact-content'>
		<h1 class='contact-title'>Отправить сообщение:</h1>
		<div class='contact-loading' style='display:none'></div>
		<div class='contact-message' style='display:none'></div>
		<form action='#' style='display:none'>
			<label for='contact-name'>*Имя:</label>
			<input type='text' id='contact-name' class='contact-input' name='name' tabindex='1001' />
			<label for='contact-email'>*Email:</label>
			<input type='text' id='contact-email' class='contact-input' name='email' tabindex='1002' />
			<label for='contact-phone'>*Моб. тел.:</label>
			<input type='text' id='contact-phone' class='contact-input' name='phone' tabindex='1003' />";

	if ($extra["form_subject"]) {
		$output .= "
			<label for='contact-subject'>Тема:</label>
			<input type='text' id='contact-subject' class='contact-input' name='subject' value='' tabindex='1004' />";
	}

	$output .= "
			<label for='contact-message'>*Текст:</label>
			<textarea id='contact-message' class='contact-input' name='message' cols='40' rows='4' tabindex='1005'></textarea>
			<br/>";

	if ($extra["form_cc"]) {
		$output .= "
			<label>&nbsp;</label>
			<input type='checkbox' id='contact-cc' name='cc' value='1' tabindex='1006' /> <span class='contact-cc'>Продублировать сообщение мне</span>
			<br/>";
	}

	$output .= "
			<label>&nbsp;</label>
			<button type='submit' class='contact-send contact-button' tabindex='1007'>Отправить</button>
			<button type='submit' class='contact-cancel contact-button simplemodal-close' tabindex='1007'>Отмена</button>
			<br/>
			<input type='hidden' name='token' value='" . smcf_token($to) . "'/>
		</form>
	</div>
	<div class='contact-bottom'></div>
</div>";

	echo $output;
}
else if ($action == "send") {
	// Send the email
	$name = isset($_POST["name"]) ? strip_tags($_POST["name"]) : "";
	$email = isset($_POST["email"]) ? $_POST["email"] : "";
	$phone = isset($_POST["phone"]) ? strip_tags($_POST["phone"]) : "";
	$subject = isset($_POST["subject"]) ? $_POST["subject"] : $subject;
	$message = isset($_POST["message"]) ? $_POST["message"] : "";
	$cc = isset($_POST["cc"]) ? $_POST["cc"] : "";
	$token = isset($_POST["token"]) ? $_POST["token"] : "";

	// make sure the token matches
	if ($token === smcf_token($to)) {
		smcf_send($name, $email, $phone, $subject, $message, $cc);
		echo "Ваше сообщение было успешно отправлено.";
	}
	else {
		echo "К сожалению, ваше сообщение не отправлено. Обновите страницу и попробуйте еще раз.";
	}
}

function smcf_token($s) {
	return md5("smcf-" . $s . date("WY"));
}

// Validate and send email
function smcf_send($name, $email, $phone, $subject, $message, $cc) {
	global $to, $extra;

	// Filter and validate fields
	$name = smcf_filter($name);
	$subject = smcf_filter($subject);
	$email = smcf_filter($email);
	if (!smcf_validate_email($email)) {
		$subject .= " - некорректный email";
		$message .= "\n\nНекорректный e-mail: $email";
		$email = $to;
		$cc = 0; // do not CC "sender"
	}

	// Add additional info to the message
	if ($extra["ip"]) {
		$message .= "\n\nIP: " . $_SERVER["REMOTE_ADDR"];
	}
	if ($extra["user_agent"]) {
		$message .= "\n\nUSER AGENT: " . $_SERVER["HTTP_USER_AGENT"];
	}

	// Set and wordwrap message body
	$body = "От: $name\n\n";
	$body .= "Телефон: $phone\n\n";
	$body .= "С сайта: " . $_SERVER['SERVER_NAME'] . "\n\n";
	$body .= "Текст сообщения: $message";
	$body = wordwrap($body, 70);

	// Build header
	$headers = "From: $email\n";
	if ($cc == 1) {
		$headers .= "Cc: $email\n";
	}
	$headers .= "X-Mailer: PHP/SimpleModalContactForm";

	// UTF-8
	$subject = '=?UTF-8?B?'.base64_encode($subject).'?=';

	$headers .= "MIME-Version: 1.0\n";
	$headers .= "Content-type: text/plain; charset=utf-8\n";
	$headers .= "Content-Transfer-Encoding: quoted-printable\n";

	// Send email
	@mail($to, $subject, $body, $headers) or 
		die("Ваше сообщение не отправлено из-за ошибки сервера.");
}

// Remove any un-safe values to prevent email injection
function smcf_filter($value) {
	$pattern = array("/\n/","/\r/","/content-type:/i","/to:/i", "/from:/i", "/cc:/i");
	$value = preg_replace($pattern, "", $value);
	return $value;
}

// Validate email address format in case client-side validation "fails"
function smcf_validate_email($email) {
	$at = strrpos($email, "@");

	// Make sure the at (@) sybmol exists and  
	// it is not the first or last character
	if ($at && ($at < 1 || ($at + 1) == strlen($email)))
		return false;

	// Make sure there aren't multiple periods together
	if (preg_match("/(\.{2,})/", $email))
		return false;

	// Break up the local and domain portions
	$local = substr($email, 0, $at);
	$domain = substr($email, $at + 1);


	// Check lengths
	$locLen = strlen($local);
	$domLen = strlen($domain);
	if ($locLen < 1 || $locLen > 64 || $domLen < 4 || $domLen > 255)
		return false;

	// Make sure local and domain don't start with or end with a period
	if (preg_match("/(^\.|\.$)/", $local) || preg_match("/(^\.|\.$)/", $domain))
		return false;

	// Check for quoted-string addresses
	// Since almost anything is allowed in a quoted-string address,
	// we're just going to let them go through
	if (!preg_match('/^"(.+)"$/', $local)) {
		// It's a dot-string address...check for valid characters
		if (!preg_match('/^[-a-zA-Z0-9!#$%*\/?|^{}`~&\'+=_\.]*$/', $local))
			return false;
	}

	// Make sure domain contains only valid characters and at least one period
	if (!preg_match("/^[-a-zA-Z0-9\.]*$/", $domain) || !strpos($domain, "."))
		return false;	

	return true;
}

