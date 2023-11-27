<?php

require_once(__DIR__ . '/recodex_lib.php');

function convertTimeToMinutes($time)
{
    list($hours, $minutes) = explode(':', $time);
    return $hours * 60 + $minutes;
}


function validateRequiredField($requiredFields) {
    $errors = array();
    $validUnboxDays = array('24', '25');

    foreach ($requiredFields as $field) {
        
        if (!isset($_POST[$field]) || empty($_POST[$field])) {
        $errors[] = $field;
        continue;
        }
        if ('unboxDay' === $field && !in_array($_POST[$field], $validUnboxDays)) {
            $errors[] = $field;
        }
    }

    return $errors;
}

function validateNotRequiredFields($notRequiredFields) {
    $errors = array();

    $fromTime = 0;
    $toTime = 100000000000000000000000;

    if (!isset($_POST['fromTime']) || !isset($_POST['toTime'])) {
        $errors = array('fromTime','toTime');
        return $errors;
    }

    foreach ($notRequiredFields as $field) {
        if (!isset($_POST[$field])){
            $errors[] = $field;
            continue;
        }
        if(empty($_POST[$field])){ continue;} 
        
        if ($field === 'fromTime' || $field === 'toTime') {
            if (!preg_match('/^([01]?[0-9]|2[0-3]):[0-5][0-9]$/', $_POST[$field])) {
                $errors[] = $field;
            }
            if ($field === 'fromTime') { $fromTime = convertTimeToMinutes($_POST[$field]); }
            if ($field === 'toTime') { $fromTime = convertTimeToMinutes($_POST[$field]); }
        }
    }
    if ($toTime < $fromTime){ $errors = array('fromTime','toTime'); }
    return $errors;
}

function validateDeliveryBoy($deliveryBoy) {
    $allowedValues = array('jesus', 'santa', 'moroz', 'hogfather', 'czpost', 'fedex');

    if (!in_array($deliveryBoy, $allowedValues)) {
        return 'deliveryBoy';
    }

    return null;
}



function validateEmailField($emailField) {
    $errors = array();

    if (!isset($_POST[$emailField]) || !filter_var($_POST[$emailField], FILTER_VALIDATE_EMAIL)) {
        $errors[] = $emailField;
    }

    return $errors;
}

function validateGiftsField($giftsField) {
    $errors = array();

    if (isset($_POST[$giftsField]) && is_array($_POST[$giftsField])) {
        $receivedGifts = $_POST[$giftsField];

        $validGifts = array(
            'socks',
            'points',
            'jarnik',
            'cash',
            'teddy',
            'other'
        );

        foreach ($receivedGifts as $gift) {
            if (!in_array($gift, $validGifts)) {
                $errors[] = $giftsField;
                break;
            }
        }
        /* if (!in_array('other', $receivedGifts) && !empty($_POST['giftCustom'])) {
            $errors[] = 'giftCustom';
        }*/

        if (in_array('other', $receivedGifts) && empty($_POST['giftCustom'])) {
            $errors[] = 'giftCustom';
        }
    }

    return $errors;
}



if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $requiredFields = array('firstName', 'lastName', 'unboxDay');
    $errors = validateRequiredField($requiredFields);

    $deliveryBoyError = validateDeliveryBoy(isset($_POST['deliveryBoy']) ? $_POST['deliveryBoy'] : '');
    if ($deliveryBoyError !== null) {
        $errors = array_merge($errors,array('deliveryBoy'));
    }
    
    $emailErrors = validateEmailField('email');
    $errors = array_merge($errors, $emailErrors);

    $notRequiredFields = array('fromTime', 'toTime');
    $notRequiredErrors = validateNotRequiredFields($notRequiredFields);
    $errors = array_merge($errors, $notRequiredErrors);    
    
    $giftsErrors = validateGiftsField('gifts');
    $errors = array_merge($errors, $giftsErrors);

    $max_lengths = array(
        'firstName' => 100,
        'lastName' => 100,
        'email' => 200,
        'fromTime' => 5,
        'toTime' => 5,
        'giftCustom' => 100
    );

    foreach ($max_lengths as $field => $length) {
        if (isset($_POST[$field]) && strlen($_POST[$field]) > $length) {
            if (!isset($errors[$field])){
                $errors[] = $field;
            }
        }
    }

    if (!empty($errors)) {
        recodex_survey_error('ERROR HAS APEARED', array_unique($errors));
    }
    else{
    
    $firstName = isset($_POST['firstName']) ? $_POST['firstName'] : '';
    $lastName = isset($_POST['lastName']) ? $_POST['lastName'] : '';
    $email = isset($_POST['email']) ? $_POST['email'] : '';
    $deliveryBoy = isset($_POST['deliveryBoy']) ? $_POST['deliveryBoy'] : '';
    $unboxDay = isset($_POST['unboxDay']) ? $_POST['unboxDay'] : '';
    $fromTime = isset($_POST['fromTime']) ? $_POST['fromTime'] : '';
    $toTime = isset($_POST['toTime']) ? $_POST['toTime'] : '';
    $giftCustom = isset($_POST['giftCustom']) ? trim($_POST['giftCustom']) : null; 
    if (isset($_POST['gifts']) && !in_array('other', $_POST['gifts']) && !empty($_POST['giftCustom'])) {
        $giftCustom = null;
    }

    
    recodex_save_survey(
        $firstName,
        $lastName,
        $email,
        $deliveryBoy,
        $unboxDay,
        !empty($fromTime) ? convertTimeToMinutes($fromTime) : null,
        !empty($toTime) ? convertTimeToMinutes($toTime) : null,
        isset($_POST['gifts']) ? $_POST['gifts'] : array(),
        $giftCustom === '' ? null : $giftCustom
    );}
    // Redirect after successful submission
    header('Location: index.php', true, 303);
    exit();
}

require __DIR__ . '/form_template.html';

?>

