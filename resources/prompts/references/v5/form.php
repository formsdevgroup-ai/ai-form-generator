<?php
defined('ACCESSIBLE') or exit('No direct script access allowed');
@session_start();

$formname = 'Application Form';
// $prompt_message = '<span class="required-info">* Required Information</span>';
if ($_POST) {

    $result_recaptcha = Main::recaptcha($recaptcha_privite, $_POST);

    if (
        empty($_POST)
    ) {


        $asterisk = '<span style="color:#FF0000; font-weight:bold;">*&nbsp;</span>';
        $prompt_message = '<div id="error-msg"><div class="message"><span>Failed to send email. Please try again.</span><br/><p class="error-close">x</p></div></div>';
    }/*  else if (!$result_recaptcha->success) {
        $prompt_message = '<div id="recaptcha-error"><div class="message"><span>Invalid <br>Recaptcha</span><p class="rclose">x</p></div></div>';
    }  */ else {

        $sig = '';
        $signaturedirectory = '';
        $signature_base64 = '';

        if (!empty($_POST['Signature'])) {
            // Use new signature handler that doesn't require GD Library
            require_once('./assets/includes/signature_handler.php');

            // fields input
            $esignaturedata = $_POST['Signature'];

            // Process signature data using new handler
            $signature_result = processSignatureData($esignaturedata);

            if ($signature_result['success']) {
                $signature_base64 = $signature_result['data'];

                // Save signature data to file for backup
                $save_result = saveSignatureData($esignaturedata, 'assets/signatures/');

                if ($save_result['success']) {
                    $sig_filename = $save_result['filename'];
                }
            }

            // FOR SIGNATURE DIRECTORY
            $signaturedirectory = '/forms/assets/signatures/';
        }

        if (MAIL_TYPE == 1) {
            $formdisclaimer = '<div style="position: relative; top: 10px; background: #eef5f8; padding: 15px 20px; border-radius: 5px; width: 660px; margin: 0 auto; text-align: center; font-family: Poppins,sans-serif; border: 1px solid #f9f9f9;  color: #6a6a6a !important;">  
					<span style="border-radius: 50%; height: 19px; display: inline-block; color: #f49d2c; font-size: 15px;   text-align: center;"></span> Please do not reply to this email. This is only a notification from your website online forms. 
					<br>To contact the person who filled out your online form, kindly use the email which is inside the form below.</div>';
        } else
            $formdisclaimer = '';


        $body = '
		
		<div class="form_table" style="width:700px; height:auto; font-size:12px; color:#6a6a6a; letter-spacing:1px; margin: 0 auto; font-family: Poppins,sans-serif;">' . $formdisclaimer . '
		<div class="container" style="background: #fff; margin-top: 30px; font-family: Poppins,sans-serif; color:#6a6a6a; box-shadow: 10px 10px 31px -7px rgba(38,38,38,0.11); -webkit-box-shadow: 10px 10px 31px -7px rgba(38,38,38,0.11); -moz-box-shadow: 10px 10px 31px -7px rgba(38,38,38,0.11);  border-radius: 5px 5px 5px 5px; border: 1px solid #eee;">
			<div class="header" style="background: #a3c7d6; padding: 30px; border-radius: 5px 5px 0px 0px; ">
				<div align="left" style="font-size:22px; font-family: Poppins,sans-serif; color:#fff; font-weight: 900;">' . $formname . '</div>
				<div align="left" style=" color: #11465E;  font-size:19px; font-family: Poppins,sans-serif;  font-style: italic; margin-top: 6px; font-weight: 900;">' . COMP_NAME . '</div>
			</div>
		<div style="padding: 13px 30px 25px 30px;">
		<table border="0" cellpadding="0" cellspacing="0" width="100%" align="center" style="font-family: Poppins,sans-serif;font-size:14px; padding-bottom: 20px;"> 

					';
        foreach ($_POST as $key => $value) {
            if ($key == 'secode')
                continue;
            elseif ($key == 'submit')
                continue;
            elseif ($key == 'g-recaptcha-response')
                continue;
            elseif (preg_match('/^TabVal/', $key))
                continue;
            elseif (preg_match('/^TabVal2/', $key))
                continue;
            elseif ($key == 'checkboxVal')
                continue;

            if (!empty($value)) {
                $key2 = str_replace('_', ' ', $key);
                if ($value == ':') {
                    $body .= ' <tr margin-bottom="10px"> <td colspan="5" height="28" class="OFDPHeading" width="100%" style=" background:#F0F0F0; margin-bottom:5px;"><b style="padding-left: 4px;">' . $key2 . '</b></td></tr>';
                } elseif ($key === 'Signature') {
                    // If signature_base64 is empty, try to process the POST value directly
                    if (empty($signature_base64)) {
                        $signature_display = getSignatureForEmail($value);
                    } else {
                        $signature_display = getSignatureForEmail($signature_base64);
                    }

                    $body .= '<tr><td class="Values1" colspan="2" height="28" align="left" width="40%" padding="100" style="line-height: normal; padding-left: 4px;text-justify: inter-word; word-wrap: anywhere; padding-right: 28px;">
                                <span style="position:relative !important;"><b>' . $key2 . '</b></span>:</td> <td class="Values2" colspan="2" height="28" align="left" width="50%" padding="10" style="line-height: normal; word-wrap: anywhere; "><span style="margin-top: 7px; position:relative;margin-left: 7px; border-collapse: collapse; display: inline-block;margin-bottom: 5px;margin-right: 7px;">' . $signature_display . '</span> </td></tr>';
                } else if ($key == "Privacy_Policy") {
                    $body .= '<tr><td colspan="3" line-height:30px">
                         <input type="checkbox" checked disabled /> I consent to the collection and processing of my personal information and, where applicable, health-related information, including any data I submit on behalf of others. This is for the purpose of evaluating or fulfilling my request, in accordance with the Privacy Policy.
         
                             </td></tr>';
                } else if ($key == "Text01") {
                    $body .= '<tr><td colspan="3" line-height:30px">
                        <b>Compassionate Home Care</b> is an Equal Opportunity Employer and dedicated to upholding a nondiscrimination policy in matters involving our clients and employees. <b>Compassionate Home Care</b> does not exclude or deny admissions or treatment of any clients, nor assign personnel or facilities based on race, color, age, religion, national origin, citizenship status, creed, gender, marital status, sexual orientation, political affiliations, medical condition, disabilities, or any other basis prohibited by law.
         
                             </td></tr>';
                } else if ($key == "Text02") {
                    $body .= '<tr><td colspan="3" line-height:30px">
                       	By signing this form, I verify that I voluntarily consent to receiving prescriptions from my provider for psychiatric medications as a part of my treatment with C&E Psychiatry Services. I also confirm that I understand and voluntarily agree to the following:
						<br><br>
						1. I am entitled to receiving information about the medications I am prescribed.
						<br>
						2. I understand that information about my medications will be provided in oral, and electronic form by my provider before any medication is prescribed.
						<br>
						3. I understand that my prescriber of record will also ask me to provide voluntary verbal consent for any new medications, medication changes, and/or the discontinuation of medications before they are ordered. Such verbal consent confirms that information about my medications was explained to my satisfaction and will be binding as noted in my health record.<br>
						4. I understand psychotropic medications may have risks that include side effects, age-related risks, rare and potentially life-threatening side effects, as well as fetal risk for pregnant women. If I am a woman and have a possibility of pregnancy, I understand that I must tell my provider immediately to assess the risks and benefits of taking my prescribed medications.<br>
						5. I acknowledge my right to refuse any medication dose or withdr aw my consent for medications at any time.<br>
						6. I understand that having psychotropic medications prescribed by a non-Rapha Psychiatry provider, except in a psychiatric urgency or emergency that warrants it, may result in immediate discharge and end my patient-provider relationships with Rapha Psychiatry.<br>
						7. Upon such discharge, I understand that I will be given a list of alternate providers in my area from which I may choose for the continuation of my psychiatric care.<br>
						8. I understand I am responsible for making those appointments immediately to prevent gaps in my care.<br>
						9. I understand that I can print this consent form at will.<br>

         
                             </td></tr>';
                } else if ($key == "Consent1") {
                    $body .= '<tr><td colspan="3" line-height:30px">
                         <input type="checkbox" checked disabled /> I give permission for the child care facility to obtain emergency medical treatment, including emergency
                    transportation, for my child if I cannot be reached immediately. I agree to be responsible for any emergency
                    medical expenses incurred. <i>(If parent/guardian refuses to sign, instructions must be attached stating what procedure
                    the facility is to follow in an iergency.)</i>
         
                             </td></tr>';
                } else if ($key == "Table01") {
                    $body .= '<tr><td colspan="3" line-height:30px">
							     <style>
								 	#tbl1{
										width: 100%;
										border-collapse: collapse;
										border: 1px solid #000;
									}
									#tbl1 th, #tbl1 td{
									border: 1px solid #000;
									}
								 </style>
								<table id="tbl1">
									<thead>
										<tr class="days" style="background: #f1f1f1;">
										<th>Monday</th>
										<th>Tuesday</th>
										<th>Wednesday</th>
										<th>Thursday</th>
										<th>Friday</th>
										</tr>
									</thead>
									<tbody>';

                    // Dynamically process all rows for Table01 (up to 10 rows * 5 columns = 50 fields)
                    $maxRows = 10;
                    $columnsPerRow = 5;

                    for ($row = 1; $row <= $maxRows; $row++) {
                        $startIndex = ($row - 1) * $columnsPerRow + 1;
                        $monday = isset($_POST['TabVal_' . $startIndex]) ? $_POST['TabVal_' . $startIndex] : '';
                        $tuesday = isset($_POST['TabVal_' . ($startIndex + 1)]) ? $_POST['TabVal_' . ($startIndex + 1)] : '';
                        $wednesday = isset($_POST['TabVal_' . ($startIndex + 2)]) ? $_POST['TabVal_' . ($startIndex + 2)] : '';
                        $thursday = isset($_POST['TabVal_' . ($startIndex + 3)]) ? $_POST['TabVal_' . ($startIndex + 3)] : '';
                        $friday = isset($_POST['TabVal_' . ($startIndex + 4)]) ? $_POST['TabVal_' . ($startIndex + 4)] : '';

                        // Only add row if at least one field has data
                        if (!empty($monday) || !empty($tuesday) || !empty($wednesday) || !empty($thursday) || !empty($friday)) {
                            $body .= '<tr>
							<td data-label="Monday">' . htmlspecialchars($monday, ENT_QUOTES) . '</td>
							<td data-label="Tuesday">' . htmlspecialchars($tuesday, ENT_QUOTES) . '</td>
							<td data-label="Wednesday">' . htmlspecialchars($wednesday, ENT_QUOTES) . '</td>
							<td data-label="Thursday">' . htmlspecialchars($thursday, ENT_QUOTES) . '</td>
							<td data-label="Friday">' . htmlspecialchars($friday, ENT_QUOTES) . '</td>
							</tr>';
                        }
                    }

                    $body .= '</tbody>
									</table>
				 
									 </td></tr>';
                } else if ($key == "Table02") {
                    $body .= '<tr><td colspan="3" line-height:30px">
							 <style>
								 #tbl2{
									width: 100%;
									border-collapse: collapse;
									border: 1px solid #000;
								}
								#tbl2 th, #tbl2 td{
								border: 1px solid #000;
								}
							 </style>
							<table id="tbl2">
								<thead>
									<tr class="days" style="background: #f1f1f1;">
									<th>Employer</th>
									<th>Dates</th>
									<th>Position</th>
									<th>Phone</th>
									</tr>
								</thead>
								<tbody>';

                    // Dynamically process all rows for Table02 (up to 10 rows * 4 columns = 40 fields)
                    $maxRows = 10;
                    $columnsPerRow = 4;

                    for ($row = 1; $row <= $maxRows; $row++) {
                        $startIndex = ($row - 1) * $columnsPerRow + 1;
                        $employer = isset($_POST['TabVal2_' . $startIndex]) ? $_POST['TabVal2_' . $startIndex] : '';
                        $dates = isset($_POST['TabVal2_' . ($startIndex + 1)]) ? $_POST['TabVal2_' . ($startIndex + 1)] : '';
                        $position = isset($_POST['TabVal2_' . ($startIndex + 2)]) ? $_POST['TabVal2_' . ($startIndex + 2)] : '';
                        $phone = isset($_POST['TabVal2_' . ($startIndex + 3)]) ? $_POST['TabVal2_' . ($startIndex + 3)] : '';

                        // Only add row if at least one field has data
                        if (!empty($employer) || !empty($dates) || !empty($position) || !empty($phone)) {
                            $body .= '<tr>
							<td data-label="Employer">' . htmlspecialchars($employer, ENT_QUOTES) . '</td>
							<td data-label="Dates">' . htmlspecialchars($dates, ENT_QUOTES) . '</td>
							<td data-label="Position">' . htmlspecialchars($position, ENT_QUOTES) . '</td>
							<td data-label="Phone">' . htmlspecialchars($phone, ENT_QUOTES) . '</td>
							</tr>';
                        }
                    }

                    $body .= '</tbody>
								</table>
			 
								 </td></tr>';
                } else {
                    $body .= '<tr><td class="Values1"colspan="2" height="28" align="left" width="40%" padding="100" style="line-height: normal; padding-left: 4px;text-justify: inter-word; word-wrap: anywhere; padding-right: 28px;">
								<span style="position:relative !important;"><b>' . $key2 . '</b></span >:</td> <td class="Values2"colspan="2" height="28" align="left" width="50%" padding="10" style="line-height: normal; word-wrap: anywhere; "><span style="margin-top: 7px; position:relative;margin-left: 7px; border-collapse: collapse; display: inline-block;margin-bottom: 5px;margin-right: 7px;">' . htmlspecialchars(trim($value), ENT_QUOTES) . '</span> </td></tr>';
                }
            }
        }
        $body .= '
					</table>
					</div>
					</div>';

        echo $body;
        exit;

        // save data form on database
        $subject2 = $formname;
        $attachments = array();


        //name of sender
        $name = $_POST['First_Name'] . ' ' . $_POST['Last_Name'];
        $result = insertDB($name, $subject2, $body, $attachments);

        $parameter = array(
            'body' => $body,
            'from' => $from_email,
            'from_name' => $from_name,
            'to' => $to_email,
            'subject' => 'New Message Notification',
            'attachment' => $attachments
        );



        $prompt_message = send_email($parameter);
        unset($_POST);
    }
}
/*************declaration starts here************/
$country = array(
    '- Please Select Country -',
    'Afghanistan',
    'Albania',
    'Algeria',
    'American Samoa',
    'Andorra',
    'Angola',
    'Anguilla',
    'Antarctica',
    'Antigua and Barbuda',
    'Argentina',
    'Armenia',
    'Aruba',
    'Australia',
    'Austria',
    'Azerbaijan',
    'Bahamas',
    'Bahrain',
    'Bangladesh',
    'Barbados',
    'Belarus',
    'Belgium',
    'Belize',
    'Benin',
    'Bermuda',
    'Bhutan',
    'Bolivia',
    'Bosnia and Herzegovina',
    'Botswana',
    'Brazil',
    'British Indian Ocean Territory',
    'Brunei Darussalam',
    'Bulgaria',
    'Burkina Faso',
    'Burundi',
    'Cambodia',
    'Cameroon',
    'Canada',
    'Cape Verde',
    'Cayman Islands',
    'Central African Republic',
    'Chad',
    'Chile',
    'China',
    'Colombia',
    'Comoros',
    'Congo',
    'Congo, Democratic Republic',
    'Costa Rica',
    'Croatia',
    'Cuba',
    'Cyprus',
    'Czech Republic',
    'Denmark',
    'Djibouti',
    'Dominica',
    'Dominican Republic',
    'Ecuador',
    'Egypt',
    'El Salvador',
    'Equatorial Guinea',
    'Eritrea',
    'Estonia',
    'Eswatini',
    'Ethiopia',
    'Fiji',
    'Finland',
    'France',
    'Gabon',
    'Gambia',
    'Georgia',
    'Germany',
    'Ghana',
    'Greece',
    'Grenada',
    'Guatemala',
    'Guinea',
    'Guinea-Bissau',
    'Guyana',
    'Haiti',
    'Honduras',
    'Hungary',
    'Iceland',
    'India',
    'Indonesia',
    'Iran',
    'Iraq',
    'Ireland',
    'Israel',
    'Italy',
    'Jamaica',
    'Japan',
    'Jordan',
    'Kazakhstan',
    'Kenya',
    'Kiribati',
    'Korea, North',
    'Korea, South',
    'Kuwait',
    'Kyrgyzstan',
    'Laos',
    'Latvia',
    'Lebanon',
    'Lesotho',
    'Liberia',
    'Libya',
    'Liechtenstein',
    'Lithuania',
    'Luxembourg',
    'Madagascar',
    'Malawi',
    'Malaysia',
    'Maldives',
    'Mali',
    'Malta',
    'Marshall Islands',
    'Mauritania',
    'Mauritius',
    'Mexico',
    'Micronesia',
    'Moldova',
    'Monaco',
    'Mongolia',
    'Montenegro',
    'Morocco',
    'Mozambique',
    'Myanmar',
    'Namibia',
    'Nauru',
    'Nepal',
    'Netherlands',
    'New Zealand',
    'Nicaragua',
    'Niger',
    'Nigeria',
    'North Macedonia',
    'Norway',
    'Oman',
    'Pakistan',
    'Palau',
    'Palestine',
    'Panama',
    'Papua New Guinea',
    'Paraguay',
    'Peru',
    'Philippines',
    'Poland',
    'Portugal',
    'Qatar',
    'Romania',
    'Russia',
    'Rwanda',
    'Saint Kitts and Nevis',
    'Saint Lucia',
    'Saint Vincent and the Grenadines',
    'Samoa',
    'San Marino',
    'Sao Tome and Principe',
    'Saudi Arabia',
    'Senegal',
    'Serbia',
    'Seychelles',
    'Sierra Leone',
    'Singapore',
    'Slovakia',
    'Slovenia',
    'Solomon Islands',
    'Somalia',
    'South Africa',
    'South Sudan',
    'Spain',
    'Sri Lanka',
    'Sudan',
    'Suriname',
    'Sweden',
    'Switzerland',
    'Syria',
    'Taiwan',
    'Tajikistan',
    'Tanzania',
    'Thailand',
    'Timor-Leste',
    'Togo',
    'Tonga',
    'Trinidad and Tobago',
    'Tunisia',
    'Turkey',
    'Turkmenistan',
    'Tuvalu',
    'Uganda',
    'Ukraine',
    'United Arab Emirates',
    'United Kingdom',
    'United States',
    'Uruguay',
    'Uzbekistan',
    'Vanuatu',
    'Venezuela',
    'Vietnam',
    'Yemen',
    'Zambia',
    'Zimbabwe'
);
$best_time_to_call = array('- Please select -', 'Mornings', 'Early Afternoon', 'Late Afternoon', 'Early Evening');
$state = array('- Please Select State -', 'Alabama', 'Alaska', 'Arizona', 'Arkansas', 'California', 'Colorado', 'Connecticut', 'Delaware', 'District Of Columbia', 'Florida', 'Georgia', 'Hawaii', 'Idaho', 'Illinois', 'Indiana', 'Iowa', 'Kansas', 'Kentucky', 'Louisiana', 'Maine', 'Maryland', 'Massachusetts', 'Michigan', 'Minnesota', 'Mississippi', 'Missouri', 'Montana', 'Nebraska', 'Nevada', 'New Hampshire', 'New Jersey', 'New Mexico', 'New York', 'North Carolina', 'North Dakota', 'Ohio', 'Oklahoma', 'Oregon', 'Pennsylvania', 'Puerto Rico', 'Rhode Island', 'South Carolina', 'South Dakota', 'Tennessee', 'Texas', 'Utah', 'Vermont', 'Virgin Islands', 'Virginia', 'Washington', 'West Virginia', 'Wisconsin', 'Wyoming');
?>
<!DOCTYPE html>
<html lang="en">
<?php
include 'config/includes/head.php';
?>


<body>

    <div class="container my-5">
        <form id="submitform" name="contact" method="post" enctype="multipart/form-data" action="" novalidate
            class="needs-validation">

            <?php if ($testform): ?>
                <div class="alert alert-warning d-flex align-items-center p-3 mb-4" role="alert"
                    style="border-radius: 8px; font-size: 1.2rem;">
                    <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" fill="currentColor" class="text-warning"
                        viewBox="0 0 24 24">
                        <path d="M1 21h22L12 2 1 21zm12-3h-2v-2h2v2zm0-4h-2v-4h2v4z" />
                    </svg>
                    <strong>You are in test mode!</strong>
                </div>

            <?php endif; ?>

            <?php echo $prompt_message; ?>


            <?php if ($step): ?>
                <div class="step step-active">
                    <div class="step-indicator">Step 1 of 3</div>
                    <?php $stepnumber = 1; ?>
                <?php endif; ?>

                <input type="checkbox" name="Text02" id="" checked hidden>
                <div class="row g-3 mb-3">
                    <div class="col-md-12">
                        By signing this form, I verify that I voluntarily consent to receiving prescriptions from my provider for psychiatric medications as a part of my treatment with C&E Psychiatry Services. I also confirm that I understand and voluntarily agree to the following:
                        <br><br>
                        1. I am entitled to receiving information about the medications I am prescribed.
                        <br>
                        2. I understand that information about my medications will be provided in oral, and electronic form by my provider before any medication is prescribed.
                        <br>
                        3. I understand that my prescriber of record will also ask me to provide voluntary verbal consent for any new medications, medication changes, and/or the discontinuation of medications before they are ordered. Such verbal consent confirms that information about my medications was explained to my satisfaction and will be binding as noted in my health record.<br>
                        4. I understand psychotropic medications may have risks that include side effects, age-related risks, rare and potentially life-threatening side effects, as well as fetal risk for pregnant women. If I am a woman and have a possibility of pregnancy, I understand that I must tell my provider immediately to assess the risks and benefits of taking my prescribed medications.<br>
                        5. I acknowledge my right to refuse any medication dose or withdr aw my consent for medications at any time.<br>
                        6. I understand that having psychotropic medications prescribed by a non-Rapha Psychiatry provider, except in a psychiatric urgency or emergency that warrants it, may result in immediate discharge and end my patient-provider relationships with Rapha Psychiatry.<br>
                        7. Upon such discharge, I understand that I will be given a list of alternate providers in my area from which I may choose for the continuation of my psychiatric care.<br>
                        8. I understand I am responsible for making those appointments immediately to prevent gaps in my care.<br>
                        9. I understand that I can print this consent form at will.<br>


                    </div>
                </div>

                <!-- Start Ben -->
                <p class="fieldheader text-center text-uppercase fw-bold py-2 mb-3">Personal Information</p>
                <input type="hidden" name="Personal_Information" value=":" />

                <div class="row g-3 mb-3">
                    <div class="col-md-4">
                        <?php
                        $input->fields('First_Name', 'letterOnly', 'First_Name', 'required');
                        ?>
                    </div>
                    <div class="col-md-4">
                        <?php
                        $input->fields('Last_Name', 'letterOnly', 'Last_Name', 'required');
                        ?>
                    </div>
                    <div class="col-md-4">
                        <?php
                        $input->fields('Middle_Initial', 'initialOnly', 'Middle_Initial', 'required');
                        ?>
                    </div>
                </div>

                <div class="row g-3 mb-3">
                    <div class="col-md-6">
                        <?php
                        $input->fields('Social Security Number', 'numberOnly', 'Social_Security_Number', 'required');
                        ?>
                    </div>
                    <div class="col-md-6">
                        <?php
                        $input->datepicker('Date_of_Birth', 'datepicker', 'required', 'Date1 DisableFuture', ' ', 'Date of Birth');
                        ?>
                    </div>
                </div>

                <p class="fieldheader text-center text-uppercase fw-bold py-2 mb-3">Primary Address</p>
                <input type="hidden" name="Primary_Address" value=":" />

                <div class="row g-3 mb-3">
                    <div class="col-md-12">
                        <?php
                        $input->fields('Street_Address', 'letterOnly', 'Street_Address', 'required');
                        ?>
                    </div>
                </div>

                <div class="row g-3 mb-3">
                    <div class="col-md-12">
                        <?php
                        $input->fields('Address_Line_2', 'letterOnly', 'Address_Line_2', '');
                        ?>
                    </div>
                </div>

                <div class="row g-3 mb-3">
                    <div class="col-md-4">
                        <?php
                        $input->fields('City', 'custom-class', 'City', 'required');
                        ?>
                    </div>
                    <div class="col-md-4">
                        <?php
                        $input->select('State', 'form-select', $state, 'State', 'required', 'State', '', 'State');
                        ?>
                    </div>

                    <div class="col-md-4">
                        <?php
                        $input->fields('Zip_Code', 'numberOnly', 'Zip_Code', 'required');
                        ?>
                    </div>
                </div>


                <p class="fieldheader text-center text-uppercase fw-bold py-2 mb-3">Secondary Address</p>
                <input type="hidden" name="Secondary_Address" value=":" />

                <div class="row g-3 mb-3">
                    <div class="col-md-12">
                        <?php
                        $input->fields('Street_Address_', 'letterOnly', 'Street_Address_', 'required');
                        ?>
                    </div>
                </div>

                <div class="row g-3 mb-3">
                    <div class="col-md-12">
                        <?php
                        $input->fields('Address_Line_2_', 'letterOnly', 'Address_Line_2_', '');
                        ?>
                    </div>
                </div>

                <div class="row g-3 mb-3">
                    <div class="col-md-6">
                        <?php
                        $input->fields('City_', 'custom-class', 'City_', 'required');
                        ?>
                    </div>
                    <div class="col-md-6">
                        <?php
                        $input->select('State_', 'form-select', $state, 'State_', 'required', 'State_', '', 'State_');
                        ?>
                    </div>
                </div>

                <div class="row g-3 mb-3">
                    <div class="col-md-6">
                        <?php
                        $input->fields('Zip_Code_', 'numberOnly', 'Zip_Code_', 'required');
                        ?>
                    </div>
                    <div class="col-md-6">
                        <?php
                        $input->select('Country', 'form-select', $country, 'Country', 'required', 'Country', '', 'Country');
                        ?>
                    </div>
                </div>

                <p class="fieldheader text-center text-uppercase fw-bold py-2 mb-3">Contact Information</p>
                <input type="hidden" name="Contact_Information" value=":" />

                <div class="row g-3 mb-3">
                    <div class="col-md-6">
                        <?php $input->phoneInput('Home Phone', '', 'phone', ''); ?>
                    </div>
                    <div class="col-md-6">
                        <?php $input->phoneInput('Cell Phone', '', 'phone', 'required'); ?>
                    </div>
                </div>

                <div class="row g-3 mb-3">
                    <div class="col-md-6">
                        <?php
                        $input->select('Best_Time_To_Call_You', 'form-select', $best_time_to_call, 'Best_Time_To_Call_You', 'required', 'Best_Time_To_Call_You', '', 'Best_Time_To_Call_You');
                        ?>
                    </div>
                </div>


                <div class="row g-3 mb-3">
                    <div class="col-md-6">
                        <?php
                        $input->email('Email_Address', '', 'Email_Address', 'required', '', '', 'Email Address');
                        ?>
                    </div>
                    <div class="col-md-6">
                        <?php
                        $input->email('Confirm_Email_Address', '', 'Confirm_Email_Address', 'required', '', '', 'Confirm Email Address');
                        ?>
                    </div>
                </div>

                <div class="row g-3 mb-3">
                    <div class="col-md-12">
                        <?php
                        $input->label('May we use e-mail to contact you?', '');
                        // @param field name, class, id, and attribute
                        $input->radio('May_We_Use_Email_to_Contact_You', array('Yes', 'No'), 'May_We_Use_Email_to_Contact_You', 'required', '2');
                        ?>
                    </div>
                </div>

                <?php if ($step): ?>
                    <div class="button-row">
                        <div class="button-col"></div>
                        <div class="button-col">
                            <button type="button" class="btn btn-primary w-100" onclick="nextStep()">Next <i
                                    class="fas fa-chevron-right"></i></button>
                        </div>
                    </div>

                </div>
            <?php endif; ?>

            <!-- End Ben -->
            <?php if ($step): ?>
                <div class="step step-active">
                    <div class="step-indicator">Step 2 of 3</div>
                    <?php $stepnumber = 2; ?>
                <?php endif; ?>
                <!-- Start Mark 1 -->

                <p class="fieldheader text-center text-uppercase fw-bold py-2 mb-3">Additional Information</p>
                <input type="hidden" name="Additional_Information" value=":" />

                <div class="row g-3 mb-3">
                    <div class="col-md-12">
                        <?php
                        $input->label('Have you ever been an employee of Compassionate Home Care?', '');
                        $input->radio('_Have_you_ever_been_an_employee_of_Compassionate_Home_Care?', array('Yes', 'No'), '_Have_you_ever_been_an_employee_of_Compassionate_Home_Care?', 'required', '2');
                        ?>
                    </div>
                </div>

                <div class="row g-3 mb-3">
                    <div class="col-md-12">
                        <?php
                        $input->label('I certify that I am a U.S. citizen, permanent resident, or a foreign national with authorization to work in the United States.', '');
                        $input->radio('_I_certify_that_I_am_a_U_S_citizen_permanent_resident_or_a_foreign_national_with_authorization_to_work_in_the_United_States', array('Yes', 'No'), '_I_certify_that_I_am_a_U_S_citizen_permanent_resident_or_a_foreign_national_with_authorization_to_work_in_the_United_States', 'required', '2');
                        ?>
                    </div>
                </div>

                <div class="row g-3 mb-3">
                    <div class="col-md-12">
                        <?php
                        $input->label('These questions must be answered in order to be considered for employment. Have you ever been convicted of, or entered a plea of guilty in a court of law?', '');
                        $input->radio('_These_questions_must_be_answered_in_order_to_be_considered_for_employment_Have_you_ever_been_convicted_of_or_entered_a_plea_of_guilty_in_a_court_of_law', array('Yes', 'No'), '_These_questions_must_be_answered_in_order_to_be_considered_for_employment_Have_you_ever_been_convicted_of_or_entered_a_plea_of_guilty_in_a_court_of_law', 'required', '2');
                        ?>
                    </div>
                </div>

                <div class="row g-3 mb-3" id="ifYes">
                    <div class="col-md-12">
                        <?php
                        $input->fields('_Please explain', '', '_Please_explain_', '');
                        ?>
                    </div>
                </div>

                <div class="row g-3 mb-3">
                    <div class="col-md-12">
                        <?php
                        $input->label('Have you ever been convicted of a felony?', '');
                        $input->radio('_Have_you_ever_been_convicted_of_a_felony?', array('Yes', 'No'), '_Have_you_ever_been_convicted_of_a_felony?', 'required', '2');
                        ?>
                    </div>
                </div>

                <div class="row g-3 mb-3" id="ifYes2">
                    <div class="col-md-12">
                        <?php

                        $input->fields('_Please explain', '', '_Please_explain__', '');
                        ?>
                    </div>
                </div>

                <hr>

                <div class="row g-3 mb-3">
                    <div class="col-md-12">
                        <?php
                        $input->label('Position You\'re Applying For', '');
                        $input->radio('_Position_You_re_Applying_For', array('Sales', 'Engineering', 'Clerical/Accounting', 'Marketing', 'IT/Technical', 'Facilities Maintenance'), '_Position_You_re_Applying_For', '', '3');
                        ?>
                    </div>
                </div>



                <!-- End Mark 1 -->

                <!-- Start Gab 1 -->

                <p class="fieldheader text-center text-uppercase fw-bold py-2 mb-3">CHILD' S PREADMISSION RECORD</p>
                <input type="hidden" name="CHILD' S PREADMISSION RECORD" value=":" />



                <div class="row g-3 mb-3">
                    <div class="col-md-12">
                        <div class="employment-history-section">
                            <h4 class="section-title">Hours You are Available for Work</h4>
                            <p class="section-subtitle">Please tell us what hours you are available for work each day of the week.</p>
                            <input type="checkbox" name="Table01" hidden checked>
                            <div class="table-responsive">
                                <table class="table employment-table" id="employmentTable">
                                    <thead>
                                        <tr>
                                            <th scope="col">Monday</th>
                                            <th scope="col">Tuesday</th>
                                            <th scope="col">Wednesday</th>
                                            <th scope="col">Thursday</th>
                                            <th scope="col">Friday</th>

                                        </tr>
                                    </thead>
                                    <tbody>
                                        <!-- Row 1 -->
                                        <tr class="table-row" data-row="1">
                                            <td data-label="Monday">
                                                <input type="text" class="form-control table-input" name="TabVal_1" required>
                                            </td>
                                            <td data-label="Tuesday">
                                                <input type="text" class="form-control table-input" name="TabVal_2" required>
                                            </td>
                                            <td data-label="Wednesday">
                                                <input type="text" class="form-control table-input" name="TabVal_3">
                                            </td>
                                            <td data-label="Thursday">
                                                <input type="text" class="form-control table-input" name="TabVal_4">
                                            </td>
                                            <td data-label="Friday">
                                                <input type="text" class="form-control table-input" name="TabVal_5">
                                            </td>
                                        </tr>

                                    </tbody>
                                </table>
                            </div>

                            <div class="table-actions mt-3">
                                <button type="button" class="btn btn-success btn-sm" id="addRowBtn">
                                    <i class="fas fa-plus"></i> Add Another Row
                                </button>
                                <button type="button" class="btn btn-danger btn-sm" id="removeRowBtn" style="display: none;">
                                    <i class="fas fa-minus"></i> Remove Last Row
                                </button>
                            </div>
                            <div class="table-note">
                                <small class="text-muted">
                                    <i class="fas fa-info-circle"></i>
                                    Note: You can add multiple rows to specify different time periods or schedules.
                                </small>
                            </div>
                        </div>
                    </div>
                </div>

                <p class="fieldheader text-center text-uppercase fw-bold py-2 mb-3">Employment History</p>
                <input type="hidden" name="Employment_History" value=":" />

                <div class="row g-3 mb-3">
                    <div class="col-md-12">
                        <div class="employment-history-section">
                            <h4 class="section-title">Your Previous Employers</h4>
                            <p class="section-subtitle">List the last five years employment history, starting with the most recent employer. </p>
                            <input type="checkbox" name="Table02" hidden checked>
                            <div class="table-responsive">
                                <table class="table employment-table" id="employmentTable2">
                                    <thead>
                                        <tr>
                                            <th scope="col">Employer</th>
                                            <th scope="col">Dates</th>
                                            <th scope="col">Position</th>
                                            <th scope="col">Phone</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <!-- Row 1 -->
                                        <tr class="table-row2" data-row="1">
                                            <td data-label="Employer">
                                                <input type="text" class="form-control table-input" name="TabVal2_1" required>
                                            </td>
                                            <td data-label="Dates">
                                                <input type="date" class="form-control table-input" name="TabVal2_2" required>
                                            </td>
                                            <td data-label="Position">
                                                <input type="text" class="form-control table-input" name="TabVal2_3">
                                            </td>
                                            <td data-label="Phone">
                                                <input type="text" class="form-control table-input" name="TabVal2_4">
                                            </td>
                                        </tr>

                                    </tbody>
                                </table>
                            </div>
                            <div class="table-actions mt-3" id="table2-actions">
                                <button type="button" class="btn btn-success btn-sm" id="addRowBtn2">
                                    <i class="fas fa-plus"></i> Add Another Row
                                </button>
                                <button type="button" class="btn btn-danger btn-sm" id="removeRowBtn2" style="display: none;">
                                    <i class="fas fa-minus"></i> Remove Last Row
                                </button>
                            </div>
                            <div class="table-note">
                                <small class="text-muted">
                                    <i class="fas fa-info-circle"></i>
                                    Note: You can add multiple rows to specify different previous employers.
                                </small>
                            </div>
                        </div>
                    </div>
                </div>

                <?php if ($step): ?>
                    <div class="button-row">
                        <div class="button-col">
                            <button type="button" class="btn btn-secondary w-100" onclick="prevStep()"><i
                                    class="fas fa-chevron-left"></i> Previous</button>
                        </div>
                        <div class="button-col">
                            <button type="button" class="btn btn-primary w-100" onclick="nextStep()">Next <i
                                    class="fas fa-chevron-right"></i></button>
                        </div>
                    </div>


                </div>
            <?php endif; ?>
            <!-- End Gab 1 -->

            <?php if ($step): ?>
                <div class="step step-active">
                    <div class="step-indicator">Step 3 of 3</div>
                    <?php $stepnumber = 3; ?>
                <?php endif; ?>

                <!-- Start Mark 2 -->

                <p class="fieldheader text-center text-uppercase fw-bold py-2 mb-3">Education</p>
                <input type="hidden" name="Education" value=":" />

                <div class="row g-3 mb-3">
                    <div class="col-md-12">
                        <?php $input->fields('_School_Attended', '', '_School_Attended', ''); ?>
                    </div>
                </div>

                <div class="row g-3 mb-3">
                    <div class="col-md-6">
                        <?php
                        $input->datepicker('_From (Month/Year)', 'datepicker', '', 'Date', ' ', 'From (Month/Year)');
                        ?>
                    </div>
                    <!-- Time Picker Field -->
                    <div class="col-md-6">
                        <?php
                        $input->datepicker('_To (Month/Year)', 'datepicker', '', 'Date', ' ', 'To (Month/Year)');
                        ?>
                    </div>
                </div>

                <div class="row g-3 mb-3">
                    <div class="col-md-12">
                        <?php
                        $input->label('Did you graduate?', '');
                        $input->radio('_Did_you_graduate?', array('Yes', 'No'), '_Did_you_graduate?', '', '2');
                        ?>
                    </div>
                </div>

                <div class="row g-3 mb-3">
                    <div class="col-md-12">
                        <?php $input->fields('_Street_Address', '', '_Street_Address', ''); ?>
                    </div>
                </div>

                <div class="row g-3 mb-3">
                    <div class="col-md-12">
                        <?php $input->fields('_Address_Line_2', '', '_Address_Line_2', ''); ?>
                    </div>
                </div>

                <div class="row g-3 mb-3">
                    <div class="col-md-4">
                        <?php
                        $input->fields('_City', '', '_City', '');
                        ?>
                    </div>
                    <div class="col-md-4">
                        <?php
                        $input->select('_State', 'form-select', $state, '_State', '', 'State', '', 'State', '');
                        ?>
                    </div>
                    <div class="col-md-4">
                        <?php
                        $input->fields('_Zip_Code', 'numberOnly', '_Zip_Code', '');
                        ?>
                    </div>
                </div>

                <div class="row g-3 mb-3">
                    <div class="col-md-12">
                        <?php $input->fields('_Type of degree or diploma', '', '_Type_of_degree_or_diploma', ''); ?>
                    </div>
                </div>

                <hr>

                <div class="row g-3 mb-3">
                    <div class="col-md-12">
                        <?php $input->fields('_School Attended', '', '_School_Attended_', ''); ?>
                    </div>
                </div>

                <div class="row g-3 mb-3">
                    <div class="col-md-6">
                        <?php
                        $input->datepicker('_From (Month/Year)_', 'datepicker', '', 'Date', ' ', 'From (Month/Year)');
                        ?>
                    </div>
                    <!-- Time Picker Field -->
                    <div class="col-md-6">
                        <?php
                        $input->datepicker('_To (Month/Year)_', 'datepicker', '', 'Date', ' ', 'To (Month/Year)');
                        ?>
                    </div>
                </div>

                <div class="row g-3 mb-3">
                    <div class="col-md-12">
                        <?php
                        $input->label('Did you graduate?', '');
                        $input->radio('_Did_you_graduate?_', array('Yes', 'No'), '_Did_you_graduate?_', '', '2');
                        ?>
                    </div>
                </div>

                <div class="row g-3 mb-3">
                    <div class="col-md-12">
                        <?php $input->fields('_Street_Address_', '', '_Street_Address_', ''); ?>
                    </div>
                </div>

                <div class="row g-3 mb-3">
                    <div class="col-md-12">
                        <?php $input->fields('_Address_Line_2_', '', '_Address_Line_2_', ''); ?>
                    </div>
                </div>

                <div class="row g-3 mb-3">
                    <div class="col-md-4">
                        <?php
                        $input->fields('_City_', '', '_City_', '');
                        ?>
                    </div>
                    <div class="col-md-4">
                        <?php
                        $input->select('_State_', 'form-select', $state, '_State_', '', 'State', '', 'State', '');
                        ?>
                    </div>
                    <div class="col-md-4">
                        <?php
                        $input->fields('_Zip_Code_', 'numberOnly', '_Zip_Code_', '');
                        ?>
                    </div>
                </div>

                <div class="row g-3 mb-3">
                    <div class="col-md-12">
                        <?php $input->fields('_Type_of_degree_or_diploma_', '', '_Type_of_degree_or_diploma_', ''); ?>
                    </div>
                </div>

                <!-- End Mark 2 -->

                <!-- Start Ken 1 -->

                <p class="fieldheader text-center text-uppercase fw-bold py-2 mb-3">Work History</p>
                <input type="hidden" name="__Work_History" value=":" />

                <div class="row g-3 mb-3">
                    <div class="col-md-12">
                        <?php $input->fields('__Job_Title', '', '__Job_Title', ''); ?>
                    </div>
                </div>

                <div class="row g-3 mb-3">
                    <div class="col-md-6">
                        <?php $input->datepicker('__From', '__From', '', 'Date1 DisableFuture', '', '__From'); ?>
                    </div>
                    <div class="col-md-6">
                        <?php $input->datepicker('__To', '__To', '', 'Date1 DisablePast', '', '__To'); ?>
                    </div>
                </div>

                <div class="row g-3 mb-3">
                    <div class="col-md-12">
                        <?php $input->fields('__Hourly_Wage', 'numberOnly', '__Hourly_Wage', ''); ?>
                    </div>
                </div>

                <div class="row g-3 mb-3">
                    <div class="col-md-12">
                        <?php $input->fields('__Employer', '', '__Employer', ''); ?>
                    </div>
                </div>

                <p class="fieldheader text-center text-uppercase fw-bold py-2 mb-3">Address</p>
                <input type="hidden" name="__Address" value=":" />

                <div class="row g-3 mb-3">
                    <div class="col-md-12">
                        <?php $input->fields('__Street_Address', '', '__Street_Address', ''); ?>
                    </div>
                </div>

                <div class="row g-3 mb-3">
                    <div class="col-md-12">
                        <?php $input->fields('__Address_Line_2', '', '__Address_Line_2', ''); ?>
                    </div>
                </div>

                <div class="row g-3 mb-3">
                    <div class="col-md-4">
                        <?php $input->fields('__City', '', '__City', ''); ?>
                    </div>
                    <div class="col-md-4">
                        <?php
                        $input->select('__State', 'form-select', $state, '__State', 'required', 'State', '', '__State');
                        ?>
                    </div>
                    <div class="col-md-4">
                        <?php $input->fields('__Zip_Code', '', '__Zip_Code', ''); ?>
                    </div>
                </div>

                <div class="row g-3 mb-3">
                    <div class="col-md-12">
                        <?php $input->phoneInput('__Phone', '', '__Phone', ''); ?>
                    </div>
                </div>

                <div class="row g-3 mb-3">
                    <div class="col-md-12">
                        <?php $input->fields('__Supervisor', '', '__Supervisor', ''); ?>
                    </div>
                </div>

                <div class="row g-3 mb-3">
                    <div class="col-md-12">
                        <?php
                        $input->label('May we contact this employer?', '');
                        $input->radio('__May_we_contact_this_employer', array('Yes', 'No'), '__May_we_contact_this_employer', '', '2');
                        ?>
                    </div>
                </div>

                <hr>

                <div class="row g-3 mb-3">
                    <div class="col-md-12">
                        <?php $input->fields('__Job_Title_', '', '__Job_Title_', ''); ?>
                    </div>
                </div>

                <div class="row g-3 mb-3">
                    <div class="col-md-6">
                        <?php $input->datepicker('__From_', '__From_', '', 'Date1 DisableFuture', '', '__From_'); ?>
                    </div>
                    <div class="col-md-6">
                        <?php $input->datepicker('__To_', '__To_', '', 'Date1 DisablePast', '', '__To_'); ?>
                    </div>
                </div>

                <div class="row g-3 mb-3">
                    <div class="col-md-12">
                        <?php $input->fields('__Hourly_Wage_', 'numberOnly', '__Hourly_Wage_', ''); ?>
                    </div>
                </div>

                <div class="row g-3 mb-3">
                    <div class="col-md-12">
                        <?php $input->fields('__Employer_', '', '__Employer_', ''); ?>
                    </div>
                </div>

                <p class="fieldheader text-center text-uppercase fw-bold py-2 mb-3">Address</p>
                <input type="hidden" name="__Address_" value=":" />

                <div class="row g-3 mb-3">
                    <div class="col-md-12">
                        <?php $input->fields('__Street_Address_', '', '__Street_Address_', ''); ?>
                    </div>
                </div>

                <div class="row g-3 mb-3">
                    <div class="col-md-12">
                        <?php $input->fields('__Address_Line_2_', '', '__Address_Line_2_', ''); ?>
                    </div>
                </div>

                <div class="row g-3 mb-3">
                    <div class="col-md-4">
                        <?php $input->fields('__City_', '', '__City_', ''); ?>
                    </div>
                    <div class="col-md-4">
                        <?php
                        $input->select('__State_', 'form-select', $state, '__State_', 'required', 'State', '', '__State_');
                        ?>
                    </div>
                    <div class="col-md-4">
                        <?php $input->fields('__Zip_Code_', '', '__Zip_Code_', ''); ?>
                    </div>
                </div>

                <div class="row g-3 mb-3">
                    <div class="col-md-12">
                        <?php $input->phoneInput('__Phone_', '', '__Phone_', ''); ?>
                    </div>
                </div>

                <div class="row g-3 mb-3">
                    <div class="col-md-12">
                        <?php $input->fields('__Supervisor_', '', '__Supervisor_', ''); ?>
                    </div>
                </div>

                <div class="row g-3 mb-3">
                    <div class="col-md-12">
                        <?php
                        $input->label('May we contact this employer?', '');
                        $input->radio('__May_we_contact_this_employer_', array('Yes', 'No'), '__May_we_contact_this_employer_', '', '2');
                        ?>
                    </div>
                </div>

                <hr>

                <div class="row g-3 mb-3">
                    <div class="col-md-12">
                        <?php $input->fields('__Job_Title__', '', '__Job_Title__', ''); ?>
                    </div>
                </div>

                <div class="row g-3 mb-3">
                    <div class="col-md-6">
                        <?php $input->datepicker('__From__', '__From__', '', 'Date1 DisableFuture', '', '__From__'); ?>
                    </div>
                    <div class="col-md-6">
                        <?php $input->datepicker('__To__', '__To__', '', 'Date1 DisablePast', '', '__To__'); ?>
                    </div>
                </div>

                <div class="row g-3 mb-3">
                    <div class="col-md-12">
                        <?php $input->fields('__Hourly_Wage__', 'numberOnly', '__Hourly_Wage__', ''); ?>
                    </div>
                </div>

                <div class="row g-3 mb-3">
                    <div class="col-md-12">
                        <?php $input->fields('__Employer__', '', '__Employer__', ''); ?>
                    </div>
                </div>

                <p class="fieldheader text-center text-uppercase fw-bold py-2 mb-3">Address</p>
                <input type="hidden" name="__Address__" value=":" />

                <div class="row g-3 mb-3">
                    <div class="col-md-12">
                        <?php $input->fields('__Street_Address__', '', '__Street_Address__', ''); ?>
                    </div>
                </div>

                <div class="row g-3 mb-3">
                    <div class="col-md-12">
                        <?php $input->fields('__Address_Line_2__', '', '__Address_Line_2__', ''); ?>
                    </div>
                </div>

                <div class="row g-3 mb-3">
                    <div class="col-md-4">
                        <?php $input->fields('__City__', '', '__City__', ''); ?>
                    </div>
                    <div class="col-md-4">
                        <?php
                        $input->select('__State__', 'form-select', $state, '__State__', 'required', 'State', '', '__State__');
                        ?>
                    </div>
                    <div class="col-md-4">
                        <?php $input->fields('__Zip_Code__', '', '__Zip_Code__', ''); ?>
                    </div>
                </div>

                <div class="row g-3 mb-3">
                    <div class="col-md-12">
                        <?php $input->phoneInput('__Phone__', '', '__Phone__', ''); ?>
                    </div>
                </div>

                <div class="row g-3 mb-3">
                    <div class="col-md-12">
                        <?php $input->fields('__Supervisor__', '', '__Supervisor__', ''); ?>
                    </div>
                </div>

                <div class="row g-3 mb-3">
                    <div class="col-md-12">
                        <?php
                        $input->label('May we contact this employer?', '');
                        $input->radio('__May_we_contact_this_employer__', array('Yes', 'No'), '__May_we_contact_this_employer__', '', '2');
                        ?>
                    </div>
                </div>

                <p class="fieldheader text-center text-uppercase fw-bold py-2 mb-3">3 Professional References</p>
                <input type="hidden" name="3_Professional_References" value=":" />

                <div class="row g-3 mb-3">
                    <div class="col-md-6">
                        <?php $input->fields('__First_Name', 'letterOnly', '__First_Name', ''); ?>
                    </div>
                    <div class="col-md-6">
                        <?php $input->fields('__Last_Name', 'letterOnly', '__Last_Name', ''); ?>
                    </div>
                </div>

                <p class="fieldheader text-center text-uppercase fw-bold py-2 mb-3">Address</p>
                <input type="hidden" name="__Address__" value=":" />

                <div class="row g-3 mb-3">
                    <div class="col-md-12">
                        <?php $input->fields('__Street_Address___', '', '__Street_Address___', ''); ?>
                    </div>
                </div>

                <div class="row g-3 mb-3">
                    <div class="col-md-12">
                        <?php $input->fields('__Address_Line_2___', '', '__Address_Line_2___', ''); ?>
                    </div>
                </div>

                <div class="row g-3 mb-3">
                    <div class="col-md-4">
                        <?php $input->fields('__City___', '', '__City___', ''); ?>
                    </div>
                    <div class="col-md-4">
                        <?php
                        $input->select('__State___', 'form-select', $state, '__State___', 'required', 'State', '', '__State___');
                        ?>
                    </div>
                    <div class="col-md-4">
                        <?php $input->fields('Zip __Zip_Code___', '', '__Zip_Code___', ''); ?>
                    </div>
                </div>

                <div class="row g-3 mb-3">
                    <div class="col-md-12">
                        <?php $input->phoneInput('__Phone___', '', '__Phone___', ''); ?>
                    </div>
                </div>

                <div class="row g-3 mb-3">
                    <div class="col-md-12">
                        <?php $input->fields('__Position', '', '__Position', ''); ?>
                    </div>
                </div>

                <hr>

                <div class="row g-3 mb-3">
                    <div class="col-md-6">
                        <?php $input->fields('__First_Name_', 'letterOnly', '__First_Name_', ''); ?>
                    </div>
                    <div class="col-md-6">
                        <?php $input->fields('__Last_Name_', 'letterOnly', '__Last_Name_', ''); ?>
                    </div>
                </div>

                <p class="fieldheader text-center text-uppercase fw-bold py-2 mb-3">Address</p>
                <input type="hidden" name="__Address___" value=":" />

                <div class="row g-3 mb-3">
                    <div class="col-md-12">
                        <?php $input->fields('__Street_Address____', '', '__Street_Address____', ''); ?>
                    </div>
                </div>

                <div class="row g-3 mb-3">
                    <div class="col-md-12">
                        <?php $input->fields('__Address_Line_2____', '', '__Address_Line_2____', ''); ?>
                    </div>
                </div>

                <div class="row g-3 mb-3">
                    <div class="col-md-4">
                        <?php $input->fields('__City____', '', '__City____', ''); ?>
                    </div>
                    <div class="col-md-4">
                        <?php
                        $input->select('__State____', 'form-select', $state, '__State____', 'required', 'State', '', '__State____');
                        ?>
                    </div>
                    <div class="col-md-4">
                        <?php $input->fields('__Zip_Code____', '', '__Zip_Code____', ''); ?>
                    </div>
                </div>

                <div class="row g-3 mb-3">
                    <div class="col-md-12">
                        <?php $input->phoneInput('__Phone____', '', '__Phone____', ''); ?>
                    </div>
                </div>

                <div class="row g-3 mb-3">
                    <div class="col-md-12">
                        <?php $input->fields('__Position_', '', '__Position_', ''); ?>
                    </div>
                </div>

                <hr>

                <div class="row g-3 mb-3">
                    <div class="col-md-6">
                        <?php $input->fields('__First_Name__', 'letterOnly', '__First_Name__', ''); ?>
                    </div>
                    <div class="col-md-6">
                        <?php $input->fields('__Last_Name__', 'letterOnly', '__Last_Name__', ''); ?>
                    </div>
                </div>

                <p class="fieldheader text-center text-uppercase fw-bold py-2 mb-3">Address</p>
                <input type="hidden" name="__Address____" value=":" />

                <div class="row g-3 mb-3">
                    <div class="col-md-12">
                        <?php $input->fields('__Street_Address_____', '', '__Street_Address_____', ''); ?>
                    </div>
                </div>

                <div class="row g-3 mb-3">
                    <div class="col-md-12">
                        <?php $input->fields('__Address_Line_2_____', '', '__Address_Line_2_____', ''); ?>
                    </div>
                </div>

                <div class="row g-3 mb-3">
                    <div class="col-md-4">
                        <?php $input->fields('__City_____', '', '__City_____', ''); ?>
                    </div>
                    <div class="col-md-4">
                        <?php
                        $input->select('__State_____', 'form-select', $state, '__State_____', 'required', 'State', '', '__State_____');
                        ?>
                    </div>
                    <div class="col-md-4">
                        <?php $input->fields('__Zip_Code_____', '', '__Zip_Code_____', ''); ?>
                    </div>
                </div>

                <div class="row g-3 mb-3">
                    <div class="col-md-12">
                        <?php $input->phoneInput('__Phone_____', '', '__Phone_____', ''); ?>
                    </div>
                </div>

                <div class="row g-3 mb-3">
                    <div class="col-md-12">
                        <?php $input->fields('__Position__', '', '__Position__', ''); ?>
                    </div>
                </div>
                <!-- End Ken 1 -->

                <!-- Start Gab 2 -->

                <p class="fieldheader text-center text-uppercase fw-bold py-2 mb-3">HOW DID YOU FIND OUT ABOUT THIS POSITION?</p>
                <input type="hidden" name="HOW DID YOU FIND OUT ABOUT THIS POSITION?" value=":" />

                <div class="row g-3 mb-3">
                    <div class="col-md-12 group" data-limit="7">
                        <?php
                        $input->label('We want to know how you heard about this job?', '');
                        $input->chkboxVal('We_want_to_know_how_you_heard_about_this_job', array('Co-worker', 'Career Fair', 'Job Service', 'Monster.com', 'Other Internet Source', 'Recruiter', 'Other'), 'We_want_to_know_how_you_heard_about_this_job', '', '3');
                        ?>
                    </div>
                </div>

                <div class="row g-3 mb-3" id="ifOther">
                    <div class="col-md-12">
                        <?php $input->fields('Please_Specify', '', 'Please_Specify', ''); ?>
                    </div>
                </div>

                <p class="fieldheader text-center text-uppercase fw-bold py-2 mb-3">HOW DID YOU FIND OUT ABOUT THIS POSITION?</p>
                <input type="hidden" name="HOW DID YOU FIND OUT ABOUT THIS POSITION?" value=":" />

                <div class="row g-3 mb-3">
                    <div class="col-md-12 group" data-limit="6">
                        <?php
                        $input->label('Job Types and Shifts', '');
                        $input->chkboxVal('Job_Types_and_Shifts', array('Full Time', 'Part Time', 'Permanent', 'Temporay', 'Day Shift', 'Night Shift'), 'Job_Types_and_Shifts', '', '3');
                        ?>
                    </div>
                </div>

                <div class="form-check mb-3 mt-3">
                    <input type="checkbox" class="form-check-input" id="Privacy_Policy_" name="Consent1" required>
                    <label class="form-check-label" for="Privacy_Policy_">I certify that all answers and statements on this application are true and complete to the best of my knowledge. I understand that all information on this job application is subject to verification and I consent to criminal history and background checks. I agree that you may contact references and educational institutions listed on this application. I also understand that should an investigation disclose untruthful or misleading answers, my application may be rejected, my name removed from consideration, or my employment with this company terminated.</label>
                    <div class="invalid-feedback">You must agree to the consent before submitting.</div>
                </div>

                <div class="row g-3 mb-3">
                    <div class="col-md-12">
                        <div class="signature-section">
                            <h4 class="signature-title">Signature</h4>
                            <div class="row">
                                <div class="col-md-8">
                                    <div class="sigPad signature-pad-container" id="signaturePad">
                                        <div class="sig sigWrapper current">
                                            <div class="typed"></div>
                                            <canvas class="pad" width="100%" height="200"></canvas>
                                            <input type="hidden" name="Signature" class="output" required>
                                        </div>
                                        <div class="signature-controls d-flex justify-content-between align-items-center mt-2">
                                            <p class="clearButton mb-0">
                                                <a href="#clear" class="btn btn-outline-danger btn-sm">
                                                    <i class="fas fa-eraser"></i> Clear Signature
                                                </a>
                                            </p>
                                            <small class="text-muted">
                                                <i class="fas fa-pen"></i> Draw your signature above
                                            </small>
                                        </div>
                                    </div>
                                    <div class="invalid-feedback signature-invalid-feedback" style="display: none;">
                                        Please provide your signature before submitting the form.
                                    </div>
                                </div>
                                <div class="col-md-4">
                                    <?php $input->dateToday('Date_Signed', 'Date_Signed', '', 'Date'); ?>
                                    <div class="signature-note mt-3">
                                        <div class="alert alert-info">
                                            <i class="fas fa-info-circle"></i>
                                            <strong>Instructions:</strong><br>
                                            • Use your mouse or finger to sign<br>
                                            • Sign clearly within the box<br>
                                            • Click "Clear" to start over
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>



                <div class="row g-3 mb-3">
                    <div class="col-md-12">
                        <?php
                        // Label with accepted file formats and size info
                        $input->label('Attach Resume <span class="subs">(accepted file formats: .doc, .docx, .pdf | Max: 25MB)</span>', '');
                        $input->files('', 'file', '', 'required', 'doc,docx,pdf', '25MB');
                        ?>
                    </div>
                </div>

                <p><input type="checkbox" name="Text01" hidden checked>
                    <b>Compassionate Home Care</b> is an Equal Opportunity Employer and dedicated to upholding a nondiscrimination policy in matters involving our clients and employees. <b>Compassionate Home Care</b> does not exclude or deny admissions or treatment of any clients, nor assign personnel or facilities based on race, color, age, religion, national origin, citizenship status, creed, gender, marital status, sexual orientation, political affiliations, medical condition, disabilities, or any other basis prohibited by law.
                </p>



                <div class="form-check mb-3 mt-3">
                    <input type="checkbox" class="form-check-input" id="Privacy_Policy" name="Privacy_Policy" required>
                    <label class="form-check-label" for="Privacy_Policy">I consent to the collection and processing of my personal information and, where applicable, health-related information, including any data I submit on behalf of others. This is for the purpose of evaluating or fulfilling my request, in accordance with the <a href="/privacy-policy" target="_blank">Privacy Policy</a>.</label>
                    <div class="invalid-feedback">You must agree to the privacy policy before submitting.</div>
                </div>

                <!-- Recaptcha and Submit Button -->
                <div class="row g-3 mb-3">
                    <div class="col-md-12">
                        <div class="form-group">
                            <div class="g-recaptcha" data-sitekey="<?php echo $recaptcha_sitekey; ?>"></div>
                            <small class="form-text text-danger" id="recaptchaError" style="display: none;">Please complete
                                the reCAPTCHA.</small>
                        </div>
                    </div>
                </div>
                <?php if ($step): ?>
                    <div class="button-row">
                        <div class="button-col mt-2">
                            <button type="button" class="btn btn-secondary w-100" onclick="prevStep()"><i
                                    class="fas fa-chevron-left"></i> Previous</button>
                        </div>
                        <div class="button-col mt-2">
                            <button type="submit" class="btn btn-primary w-100">Submit <i
                                    class="fas fa-angle-double-right"></i></button>
                        </div>
                    </div>


                </div>
            <?php endif; ?>
            <?php if (!$step): ?>
                <button type="submit" class="btn btn-primary w-100 mt-3 p-10" id="submissionbutton"><span>Submit <i
                            class="fas fa-angle-double-right"></i></span></button>
            <?php endif; ?>
        </form>
    </div>
    <?php $input->phone(true); ?>
    <!-- Google Recaptcha Script -->
    <script src="https://www.google.com/recaptcha/api.js"></script>

    <!-- Bootstrap 5 JavaScript -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
    <!-- Clockpicker JS (Alternative CDN) -->
    <script src="https://cdn.jsdelivr.net/npm/clockpicker@0.0.7/dist/bootstrap-clockpicker.min.js"></script>

    <script type="text/javascript" src="../assets/js/jquery.datepick.min.js"></script>
    <script src="../assets/js/datepicker.js"></script>
    <script src="../assets/js/plugins.js"></script>
    <script src="../assets/js/jquery.mask.min.js"></script>
    <script src="../assets/js/proweaverPhone.js"></script>
    <link rel="stylesheet" href="../assets/js/jquery.signaturepad.css">
    <script src="../assets/js/jquery.signaturepad.js"></script>
    <script>
        let currentStep = 0;
        const steps = document.querySelectorAll(".step");

        // Show only the current step
        function showStep(step) {
            steps.forEach((el, index) => {
                el.classList.toggle("step-active", index === step);
                if (index === step) {
                    el.style.display = "";
                } else {
                    el.style.display = "none";
                }
            });
        }

        // Validate all required fields in the current step using Bootstrap validation
        function validateStep(step) {
            let valid = true;
            const form = document.getElementById('submitform');
            const fields = steps[step].querySelectorAll("input, select, textarea");

            fields.forEach(field => {
                if (field.hasAttribute('required')) {
                    if (!field.value || (field.type === "checkbox" && !field.checked)) {
                        field.classList.add("is-invalid");
                        valid = false;
                    } else {
                        field.classList.remove("is-invalid");
                        field.classList.add("is-valid");
                    }
                }
                if (field.type === "email" && field.hasAttribute('required')) {
                    if (!field.checkValidity()) {
                        field.classList.add("is-invalid");
                        valid = false;
                    }
                }
                if (field.type === "radio" && field.hasAttribute('required')) {
                    if (!field.checkValidity()) {
                        field.classList.add("is-invalid");
                        valid = false;
                    }
                }
            });

            return valid;
        }

        function nextStep() {
            if (validateStep(currentStep)) {
                if (currentStep < steps.length - 1) {
                    currentStep++;
                    showStep(currentStep);
                }
            }
        }

        function prevStep() {
            if (currentStep > 0) {
                currentStep--;
                showStep(currentStep);
            }
        }

        // Real-time validation feedback for Bootstrap
        document.addEventListener('input', function(e) {
            if (e.target.form === document.getElementById('submitform')) {
                if (e.target.hasAttribute('required')) {
                    if (!e.target.value || (e.target.type === "checkbox" && !e.target.checked)) {
                        e.target.classList.add("is-invalid");
                        e.target.classList.remove("is-valid");
                    } else {
                        e.target.classList.remove("is-invalid");
                        e.target.classList.add("is-valid");
                    }
                }
                if (e.target.type === "email" && e.target.hasAttribute('required')) {
                    if (!e.target.checkValidity()) {
                        e.target.classList.add("is-invalid");
                        e.target.classList.remove("is-valid");
                    } else {
                        e.target.classList.remove("is-invalid");
                        e.target.classList.add("is-valid");
                    }
                }
            }
        });

        // Prevent form submission if invalid
        document.getElementById('submitform').addEventListener('submit', function(e) {
            let formValid = true;
            steps.forEach((stepDiv, idx) => {
                if (!validateStep(idx)) {
                    formValid = false;
                    if (currentStep !== idx) {
                        currentStep = idx;
                        showStep(currentStep);
                    }
                }
            });
            if (!formValid) {
                e.preventDefault();
                e.stopPropagation();
            }
        });

        showStep(currentStep);
    </script>

    <!-- Clockpicker Initialization Script -->
    <script>
        $(document).ready(function() {

            $('#ifOther').hide();
            $("#We_want_to_know_how_you_heard_about_this_job_7").change(function() {
                if ($(this).is(':checked')) {
                    $("#ifOther").fadeIn();
                    $("#ifOther").find(':input').attr('disabled', false);
                } else {
                    $("#ifOther").fadeOut();
                    $("#ifOther").find(':input').attr('disabled', 'disabled');
                }
            });

            $('#ifYes').hide();
            $("input[name='_These_questions_must_be_answered_in_order_to_be_considered_for_employment_Have_you_ever_been_convicted_of_or_entered_a_plea_of_guilty_in_a_court_of_law']").change(function() {
                if ($(this).val() == "Yes") {
                    $("#ifYes").slideDown();
                    $("#ifYes").find(':input').attr('disabled', false);
                } else {
                    $("#ifYes").slideUp();
                    $("#ifYes").find(':input').attr('disabled', 'disabled');
                }
            });

            $('#ifYes2').hide();
            $("input[name='_Have_you_ever_been_convicted_of_a_felony?']").change(function() {
                if ($(this).val() == "Yes") {
                    $("#ifYes2").slideDown();
                    $("#ifYes2").find(':input').attr('disabled', false);
                } else {
                    $("#ifYes2").slideUp();
                    $("#ifYes2").find(':input').attr('disabled', 'disabled');
                }
            });


            updateCheckboxValues('Job_Types_and_Shifts');
            updateCheckboxValues('We_want_to_know_how_you_heard_about_this_job');


            // Signature pads are now automatically initialized by the enhanced jquery.signaturepad.js
            // No manual initialization needed here

            // Table row cloning functionality
            let rowCounter = 1;
            const maxRows = 10; // Maximum number of rows allowed

            // Add row functionality
            $('#addRowBtn').click(function() {
                if (rowCounter < maxRows) {
                    rowCounter++;
                    const newRow = createTableRow(rowCounter);
                    $('#employmentTable tbody').append(newRow);

                    // Show remove button if we have more than 1 row
                    if (rowCounter > 1) {
                        $('#removeRowBtn').show();
                    }

                    // Hide add button if we reach max rows
                    if (rowCounter >= maxRows) {
                        $('#addRowBtn').hide();
                    }
                }
            });

            // Remove row functionality
            $('#removeRowBtn').click(function() {
                if (rowCounter > 1) {
                    $('#employmentTable tbody tr:last').remove();
                    rowCounter--;

                    // Hide remove button if we're back to 1 row
                    if (rowCounter === 1) {
                        $('#removeRowBtn').hide();
                    }

                    // Show add button if it was hidden
                    if (rowCounter < maxRows) {
                        $('#addRowBtn').show();
                    }
                }
            });

            // Function to create a new table row
            function createTableRow(rowNumber) {
                const startIndex = (rowNumber - 1) * 5 + 1;
                return `
			<tr class="table-row" data-row="${rowNumber}">
			<td data-label="Monday">
				<input type="text" class="form-control table-input" name="TabVal_${startIndex}">
			</td>
			<td data-label="Tuesday">
				<input type="text" class="form-control table-input" name="TabVal_${startIndex + 1}">
			</td>
			<td data-label="Wednesday">
				<input type="text" class="form-control table-input" name="TabVal_${startIndex + 2}">
			</td>
			<td data-label="Thursday">
				<input type="text" class="form-control table-input" name="TabVal_${startIndex + 3}">
			</td>
			<td data-label="Friday">
				<input type="text" class="form-control table-input" name="TabVal_${startIndex + 4}">
			</td>
			</tr>
			`;
            }

            // Table 2 row cloning functionality
            let rowCounter2 = 1;
            const maxRows2 = 10;
            $('#addRowBtn2').click(function() {
                if (rowCounter2 < maxRows2) {
                    rowCounter2++;
                    const newRow = createTableRow2(rowCounter2);
                    $('#employmentTable2 tbody').append(newRow);
                    if (rowCounter2 > 1) {
                        $('#removeRowBtn2').show();
                    }
                    if (rowCounter2 >= maxRows2) {
                        $('#addRowBtn2').hide();
                    }
                }
            });
            $('#removeRowBtn2').click(function() {
                if (rowCounter2 > 1) {
                    $('#employmentTable2 tbody tr:last').remove();
                    rowCounter2--;
                    if (rowCounter2 === 1) {
                        $('#removeRowBtn2').hide();
                    }
                    if (rowCounter2 < maxRows2) {
                        $('#addRowBtn2').show();
                    }
                }
            });

            function createTableRow2(rowNumber) {
                const startIndex = (rowNumber - 1) * 4 + 1;
                return `
					<tr class="table-row2" data-row="${rowNumber}">
					<td data-label="Employer">
						<input type="text" class="form-control table-input" name="TabVal2_${startIndex}" required>
					</td>
					<td data-label="Dates">
						<input type="date" class="form-control table-input" name="TabVal2_${startIndex + 1}" required>
					</td>
					<td data-label="Position">
						<input type="text" class="form-control table-input" name="TabVal2_${startIndex + 2}">
					</td>
					<td data-label="Phone">
						<input type="text" class="form-control table-input" name="TabVal2_${startIndex + 3}">
					</td>
					</tr>
					`;
            }
        });
    </script>







    <style>
        .employment-history-section {
            background: #f8f9fa;
            border-radius: 10px;
            padding: 25px;
            margin: 20px 0;
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.08);
            border: 1px solid #e9ecef;
        }

        .section-title {
            color: #495057;
            font-weight: 600;
            margin-bottom: 8px;
            font-size: 1.3rem;
            border-bottom: 2px solid #007bff;
            padding-bottom: 8px;
            display: inline-block;
        }

        .section-subtitle {
            color: #6c757d;
            font-size: 0.95rem;
            margin-bottom: 20px;
            font-style: italic;
        }

        .employment-table {
            background: white;
            border-radius: 8px;
            overflow: hidden;
            box-shadow: 0 1px 3px rgba(0, 0, 0, 0.1);
            margin-bottom: 15px;
        }

        .employment-table thead {
            background: linear-gradient(135deg, #007bff 0%, #0056b3 100%);
        }

        .employment-table thead th {
            color: white;
            font-weight: 600;
            border: none;
            padding: 15px 10px;
            font-size: 0.9rem;
            text-align: center;
            vertical-align: middle;
        }

        .employment-table tbody tr {
            transition: all 0.3s ease;
        }

        .employment-table tbody tr:hover {
            background-color: #f1f3f4;
            transform: translateY(-1px);
            box-shadow: 0 2px 5px rgba(0, 0, 0, 0.1);
        }

        .employment-table td {
            padding: 12px 8px;
            vertical-align: middle;
            border: 1px solid #e9ecef;
        }

        .table-input {
            border: 2px solid #e9ecef;
            border-radius: 6px;
            font-size: 0.9rem;
            padding: 8px 12px;
            transition: all 0.3s ease;
            background: white;
        }

        .table-input:focus {
            border-color: #007bff;
            box-shadow: 0 0 0 0.2rem rgba(0, 123, 255, 0.25);
            background: #fff;
        }

        .table-input.is-invalid {
            border-color: #dc3545;
        }

        .table-input.is-valid {
            border-color: #28a745;
        }

        .table-note {
            background: #e3f2fd;
            padding: 10px 15px;
            border-radius: 6px;
            border-left: 4px solid #2196f3;
        }

        .table-note i {
            color: #2196f3;
            margin-right: 8px;
        }

        /* Responsive Design using data-label approach */
        @media only screen and (max-width: 850px) {
            .employment-history-section {
                padding: 15px 10px;
                margin: 15px 0;
            }

            .section-title {
                font-size: 1.1rem;
            }

            #employmentTable,
            #employmentTable thead,
            #employmentTable tbody,
            #employmentTable th,
            #employmentTable td,
            #employmentTable tr {
                border: 0;
            }

            #employmentTable thead {
                display: none;
            }

            #employmentTable td {
                display: block;
                width: 100% !important;
                padding: 8px;
            }

            #employmentTable td:before {
                content: attr(data-label);
                background: #007bff;
                color: white;
                padding: 8px 12px;
                display: block;
                font-weight: bold;
                margin-bottom: 5px;
                border-radius: 4px;
            }

            #employmentTable tr {
                margin-bottom: 20px;
                display: inline-block;
                width: 100%;
                border: 2px solid #e9ecef;
                border-radius: 8px;
                padding: 15px;
                background: white;
                box-shadow: 0 2px 8px rgba(0, 0, 0, 0.1);
            }

            .table-input {
                width: 100%;
                margin-bottom: 10px;
            }
        }

        @media only screen and (max-width: 540px) {
            .employment-history-section {
                padding: 10px 5px;
            }

            .section-title {
                font-size: 1rem;
            }

            #employmentTable,
            #employmentTable thead,
            #employmentTable tbody,
            #employmentTable th,
            #employmentTable td,
            #employmentTable tr {
                border: 0;
            }

            #employmentTable thead {
                display: none;
            }

            #employmentTable td {
                display: block;
                width: 100% !important;
                padding: 6px;
            }

            #employmentTable td:before {
                content: attr(data-label);
                background: #007bff;
                color: white;
                padding: 6px 10px;
                display: block;
                font-weight: bold;
                margin-bottom: 5px;
                border-radius: 4px;
                font-size: 0.9rem;
            }

            #employmentTable tr {
                margin-bottom: 15px;
                display: inline-block;
                width: 100%;
                border: 1px solid #e9ecef;
                border-radius: 6px;
                padding: 10px;
                background: white;
            }

            .table-input {
                font-size: 0.9rem;
                padding: 8px 10px;
            }
        }

        /* Table Actions Styling */
        .table-actions {
            display: flex;
            gap: 10px;
            align-items: center;
            justify-content: center;
            padding: 15px 0;
            background: #f8f9fa;
            border-radius: 8px;
            margin: 15px 0;
        }

        .table-actions .btn {
            transition: all 0.3s ease;
            font-weight: 500;
        }

        .table-actions .btn:hover {
            transform: translateY(-2px);
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.15);
        }

        /* Custom scrollbar for table */
        .table-responsive::-webkit-scrollbar {
            height: 8px;
        }

        .table-responsive::-webkit-scrollbar-track {
            background: #f1f1f1;
            border-radius: 4px;
        }

        .table-responsive::-webkit-scrollbar-thumb {
            background: #c1c1c1;
            border-radius: 4px;
        }

        .table-responsive::-webkit-scrollbar-thumb:hover {
            background: #a8a8a8;
        }

        /* Enhanced Signature Pad Styling */
        .signature-section {
            background: #f8f9fa;
            border-radius: 10px;
            padding: 25px;
            margin: 20px 0;
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.08);
            border: 1px solid #e9ecef;
        }

        .signature-title {
            color: #495057;
            font-weight: 600;
            margin-bottom: 20px;
            font-size: 1.3rem;
            border-bottom: 2px solid #007bff;
            padding-bottom: 8px;
            display: inline-block;
        }

        .signature-pad-container {
            position: relative;
        }

        .sigPad {
            margin: 0;
            padding: 0;
            width: 100%;
            max-width: 100%;
            border: 3px solid #007bff;
            border-radius: 8px;
            background: white;
            overflow: hidden;
            position: relative;
        }

        .sigPad canvas.pad {
            border: none;
            background: white;
            width: 100% !important;
            height: 200px !important;
            border-radius: 5px;
        }

        .signature-controls {
            padding: 10px 15px;
            background: #f8f9fa;
            border-top: 1px solid #e9ecef;
        }

        .clearButton a {
            text-decoration: none;
            color: #dc3545;
            font-weight: 500;
        }

        .clearButton a:hover {
            color: #c82333;
        }

        .signature-note .alert-info {
            background-color: #e3f2fd;
            border-color: #2196f3;
            color: #0d47a1;
        }

        /* Ensure all signature pads have identical styling */
        #signaturePad,
        #signaturePad2,
        #signaturePad3,
        #signaturePad4,
        #signaturePad5 {
            border: 3px solid #007bff !important;
            border-radius: 8px !important;
            background: white !important;
        }

        #signaturePad canvas.pad,
        #signaturePad2 canvas.pad,
        #signaturePad3 canvas.pad,
        #signaturePad4 canvas.pad,
        #signaturePad5 canvas.pad {
            border: none !important;
            background: white !important;
            width: 100% !important;
            height: 200px !important;
        }

        /* Error styling for signature validation */
        .sigPad.signature-error {
            border-color: #dc3545 !important;
            box-shadow: 0 0 0 0.2rem rgba(220, 53, 69, 0.25) !important;
        }

        .signature-invalid-feedback {
            display: block !important;
            color: #dc3545;
            font-size: 0.875rem;
            margin-top: 0.25rem;
        }

        /* Responsive design for signature pads */
        @media (max-width: 768px) {
            .signature-section {
                padding: 15px;
                margin: 15px 0;
            }

            .signature-title {
                font-size: 1.1rem;
            }

            .sigPad canvas.pad {
                height: 150px !important;
            }

            .signature-controls {
                flex-direction: column;
                gap: 10px;
            }
        }

        @media (max-width: 480px) {
            .sigPad canvas.pad {
                height: 120px !important;
            }

            .signature-section {
                padding: 10px;
            }
        }
    </style>
    <!-- Form Validation Script -->


</body>

</html>