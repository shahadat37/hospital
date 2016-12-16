<?php
/**
 * CodeIgniter
 *
 * An open source application development framework for PHP
 *
 * This content is released under the MIT License (MIT)
 *
 * Copyright (c) 2014 - 2015, British Columbia Institute of Technology
 *
 * Permission is hereby granted, free of charge, to any person obtaining a copy
 * of this software and associated documentation files (the "Software"), to deal
 * in the Software without restriction, including without limitation the rights
 * to use, copy, modify, merge, publish, distribute, sublicense, and/or sell
 * copies of the Software, and to permit persons to whom the Software is
 * furnished to do so, subject to the following conditions:
 *
 * The above copyright notice and this permission notice shall be included in
 * all copies or substantial portions of the Software.
 *
 * THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND, EXPRESS OR
 * IMPLIED, INCLUDING BUT NOT LIMITED TO THE WARRANTIES OF MERCHANTABILITY,
 * FITNESS FOR A PARTICULAR PURPOSE AND NONINFRINGEMENT. IN NO EVENT SHALL THE
 * AUTHORS OR COPYRIGHT HOLDERS BE LIABLE FOR ANY CLAIM, DAMAGES OR OTHER
 * LIABILITY, WHETHER IN AN ACTION OF CONTRACT, TORT OR OTHERWISE, ARISING FROM,
 * OUT OF OR IN CONNECTION WITH THE SOFTWARE OR THE USE OR OTHER DEALINGS IN
 * THE SOFTWARE.
 *
 * @package	CodeIgniter
 * @author	EllisLab Dev Team
 * @copyright	Copyright (c) 2008 - 2014, EllisLab, Inc. (http://ellislab.com/)
 * @copyright	Copyright (c) 2014 - 2015, British Columbia Institute of Technology (http://bcit.ca/)
 * @license	http://opensource.org/licenses/MIT	MIT License
 * @link	http://codeigniter.com
 * @since	Version 1.0.0
 * @filesource
 */
defined('BASEPATH') OR exit('No direct script access allowed');

$lang['email_must_be_array'] = 'The validation method e-mail must be passed an array.';
$lang['email_invalid_address'] = 'Email address is not valid: %s';
$lang['email_attachment_missing'] = 'Unable to locate the following email attachment: %s';
$lang['email_attachment_unreadable'] = 'Unable to open this attachment: %s';
$lang['email_no_from'] = 'You can not send mail without "From".';
$lang['email_no_recipients'] = 'You must include the recipients: A, Cc, o Ccn ';
$lang['email_send_failure_phpmail'] = 'Unable to send email using PHP mail (). The server may be configured to send mail with this method.';
$lang['email_send_failure_sendmail'] = 'You can not send e-mail using PHP Sendmail. The server may be configured to send mail with this method.';
$lang['email_send_failure_smtp'] = 'You can not send e-mail using SMTP PHP. The server may be configured to send mail with this method.';
$lang['email_sent'] = 'The message was successfully sent using the following protocol: %s';
$lang['email_no_socket'] = 'Unable to open a socket to Sendmail. Please check your settings.';
$lang['email_no_hostname'] = 'It is not specified the SMTP host name.';
$lang['email_smtp_error'] = 'It occurred the following SMTP error: %s';
$lang['email_no_smtp_unpw'] = 'Error: You must assign a user name and SMTP password.';
$lang['email_failed_smtp_login'] = 'Can not send the authorization command access. Error: %s';
$lang['email_smtp_auth_un'] = 'Failed to authenticate the user name. Error: %s';
$lang['email_smtp_auth_pw'] = 'Failed to authenticate the password. Errore: %s';
$lang['email_smtp_data_failure'] = 'Can not send data: %s';
$lang['email_exit_status'] = 'Output status code: %s';
