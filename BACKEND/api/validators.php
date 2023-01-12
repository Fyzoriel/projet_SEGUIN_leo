<?php
include_once 'HttpError.php';
include_once 'models/Addresses.php';

/**
 * @param $addresses
 * @return HttpError|null
 */
function validateAddresses($addresses): ?HttpError {
    if (!isset($addresses)) {
        return new HttpError(400, "Bad Request", "Missing addresses");
    }
    if (!is_array($addresses)) {
        return new HttpError(400, 'Bad Request', 'Addresses must be an array');
    }
    if (count($addresses) === 0) {
        return new HttpError(400, 'Bad Request', 'Addresses must not be empty');
    }

    for ($i = 0; $i < count($addresses); $i++) {
        $error = validatePostAddress($addresses[$i]);
        if ($error) {
            return new HttpError(400, 'Bad Request', 'Address ' . $i . ' is invalid: ' . $error->getDetails());
        }
    }

    return null;
}

/**
 * @param $address
 * @return HttpError|null
 */
function validatePostAddress($address): ?HttpError {
    if (!isset($address['state'])) {
        return new HttpError(400, 'Bad Request', 'State is required');
    }
    if (!isset($address['zip'])) {
        return new HttpError(400, 'Bad Request', 'Zip is required');
    }
    if (!isset($address['city'])) {
        return new HttpError(400, 'Bad Request', 'City is required');
    }
    if (!isset($address['street'])) {
        return new HttpError(400, 'Bad Request', 'Street is required');
    }
    if (!isset($address['streetNumber'])) {
        return new HttpError(400, 'Bad Request', 'Street number is required');
    }
    if (!isset($address['type'])) {
        return new HttpError(400, 'Bad Request', 'Type is required');
    }
    if ($address['type'] != 'BILLING' && $address['type'] != 'DELIVERY') {
        return new HttpError(400, 'Bad Request', 'Type must be either BILLING or DELIVERY');
    }
    if (!isset($address['name'])) {
        return new HttpError(400, 'Bad Request', 'Name is required');
    }

    return null;
}

/**
 * @param $login
 * @return HttpError|null
 */
function validatePostLogin($login): ?HttpError {
    if (!isset($login['email'])) {
        return new HttpError(400, 'Bad Request', 'Email is required');
    }
    if (!isset($login['passphrase'])) {
        return new HttpError(400, 'Bad Request', 'Passphrase is required');
    }

    return null;
}

/**
 * @param $user
 * @return HttpError|null
 */
function validatePostRegister($user): ?HttpError {
    if (!isset($user['firstName'])) {
        return new HttpError(400, 'Bad Request', 'First name is required');
    }
    if (!isset($user['name'])) {
        return new HttpError(400, 'Bad Request', 'Name is required');
    }
    if (!isset($user['email'])) {
        return new HttpError(400, 'Bad Request', 'Email is required');
    }
    if (!isset($user['passphrase'])) {
        return new HttpError(400, 'Bad Request', 'Passphrase is required');
    }
    if (!isset($user['confirmPassphrase'])) {
        return new HttpError(400, 'Bad Request', 'Confirm passphrase is required');
    }

    if (!isset($user['role'])) {
        return new HttpError(400, 'Bad Request', 'Role is required');
    }
    $role = $user['role'];
    if ($role != 'ADMIN' && $role != 'CLIENT' && $role != 'SELLER') {
        return new HttpError(400, 'Bad Request', 'Role must be either ADMIN, CLIENT or SELLER');
    }

    // user dependant validation
    if ($role == 'CLIENT') {
        if (!isset($user['phone'])) {
            return new HttpError(400, 'Bad Request', 'Phone is required');

        }
        if (!preg_match("/(0|\+33 ?)[1-9]([-. ]?[0-9]{2} ?){3}([-. ]?[0-9]{2})/", $user['phone'])) {
            return new HttpError(400, 'Bad Request', 'Phone is not valid');
        }
        $addressesValidation = validateAddresses($user['addresses']);
        if ($addressesValidation) {
            return $addressesValidation;
        }

        if (isset($user['manufacturer'])) {
            return new HttpError(400, 'Bad Request', 'Manufacturer is not allowed for clients');
        }
    }
    // seller dependant validation
    if ($role == 'SELLER') {
        if (!isset($user['manufacturer'])) {
            return new HttpError(400, 'Bad Request', 'Manufacturer is required');
        }
        if (!isset($user['manufacturer']['id']) && !isset($user['manufacturer']['name'])) {
            return new HttpError(400, 'Bad Request', 'Manufacturer must contain id or name');
        }
        if (isset($user['manufacturer']['id']) && isset($user['manufacturer']['name'])) {
            return new HttpError(400, 'Bad Request', 'Manufacturer must contain id or name, not both');
        }

        if (isset($user['phone'])) {
            return new HttpError(400, 'Bad Request', 'Phone is not allowed for sellers');
        }
        if (isset($user['addresses'])) {
            return new HttpError(400, 'Bad Request', 'Addresses are not allowed for sellers');
        }
    }

    // admin dependant validation
    if ($role == 'ADMIN') {
        if (isset($user['phone'])) {
            return new HttpError(400, 'Bad Request', 'Phone is not allowed for admins');
        }
        if (isset($user['addresses'])) {
            return new HttpError(400, 'Bad Request', 'Addresses are not allowed for admins');
        }
        if (isset($user['manufacturer'])) {
            return new HttpError(400, 'Bad Request', 'Manufacturer is not allowed for admins');
        }
    }

    // email regex check without filter_var
    if (!preg_match('/^[a-zA-Z0-9._+-]+@[a-zA-Z0-9.-]+\.[a-zA-Z]{2,4}$/', $user['email'])) {
        return new HttpError(400, 'Bad Request', 'Email is not valid');
    }
    if ($user['passphrase'] != $user['confirmPassphrase']) {
        return new HttpError(400, 'Bad Request', 'Passphrases must be equal');
    }

    return null;
}

/**
 * @param $authorization
 * @param $id
 * @return HttpError|null
 */
function validateGetUser($authorization, $id): ?HttpError {

    if (!isset($id)) {
        return new HttpError(400, 'Bad Request', 'Id is required');
    }
    if (!isset($authorization)) {
        return new HttpError(401, 'Unauthorized', 'Authorization header is required');
    }
    $bearer = explode(" ", $authorization);

    if (count($bearer) < 2 || $bearer[0] != "Bearer") {
        return new HttpError(401, 'Unauthorized', 'Authorization header must be a Bearer token');
    }

    $jwt = $bearer[1];
    $jswDecoded = decodeJwT($jwt);

    if ($jswDecoded == null) {
        return new HttpError(401, 'Unauthorized', 'Invalid token');
    }

    if ($jswDecoded["userid"] != $id && $jswDecoded["role"] != "ADMIN") {
        return new HttpError(401, 'Unauthorized', 'You are not allowed to access this resource');
    }

    return null;
}

/**
 * @param $authorization
 * @param $id
 * @param $body
 * @return HttpError|null
 */
function validatePatchUser($authorization, $id, $body): ?HttpError {
    if (!isset($id)) {
        return new HttpError(400, 'Bad Request', 'Id is required');
    }
    if (!isset($authorization)) {
        return new HttpError(401, 'Unauthorized', 'Authorization header is required');
    }
    $bearer = explode(" ", $authorization);

    if (count($bearer) < 2 || $bearer[0] != "Bearer") {
        return new HttpError(401, 'Unauthorized', 'Authorization header must be a Bearer token');
    }

    $jwt = $bearer[1];
    $jswDecoded = decodeJwT($jwt);

    if ($jswDecoded == null) {
        return new HttpError(401, 'Unauthorized', 'Invalid token');
    }
    if ($jswDecoded["userid"] != $id && $jswDecoded["role"] != "ADMIN") {
        return new HttpError(401, 'Unauthorized', 'You are not allowed to access this resource');
    }

    return null;
}

function validatePatchUserBody($body, $role) {
    if (!isset($body)) {
        return new HttpError(400, 'Bad Request', 'Body is required');
    }
    if (!isset($body['firstName'])) {
        return new HttpError(400, 'Bad Request', 'First name is required');
    }
    if (!isset($body['name'])) {
        return new HttpError(400, 'Bad Request', 'Name is required');
    }
    if (!isset($body['email'])) {
        return new HttpError(400, 'Bad Request', 'Email is required');
    }

    if ($role == 'CLIENT') {
        if (isset($body['manufacturer'])) {
            return new HttpError(400, 'Bad Request', 'Manufacturer is not allowed for clients');
        }
        if (!isset($body['phone'])) {
            return new HttpError(400, 'Bad Request', 'Phone is required');
        }
        if (!preg_match("/(0|\+33 ?)[1-9]([-. ]?[0-9]{2} ?){3}([-. ]?[0-9]{2})/", $body['phone'])) {
            return new HttpError(400, 'Bad Request', 'Phone is not valid');
        }
        $addressesValidation = validateAddresses($body['addresses']);
        if ($addressesValidation) {
            return $addressesValidation;
        }
    }

    if (!preg_match('/^[a-zA-Z0-9._+-]+@[a-zA-Z0-9.-]+\.[a-zA-Z]{2,4}$/', $body['email'])) {
        return new HttpError(400, 'Bad Request', 'Email is not valid');
    }

    if ((isset($body['passphrase']) && !isset($body['oldPassphrase'])
        || (!isset($body['passphrase']) && isset($body['oldPassphrase'])))) {
        return new HttpError(400, 'Bad Request', 'Passphrase, confirmPassphrase and oldPassphrase must be set together');
    }

    if ((isset($body['passphrase']) && !isset($body['confirmPassphrase']))
        || (!isset($body['passphrase']) && isset($body['confirmPassphrase']))) {
        return new HttpError(400, 'Bad Request', 'Passphrase, confirmPassphrase and oldPassphrase must be set together');
    }

    if (isset($body['passphrase']) && isset($body['confirmPassphrase'])) {
        if ($body['passphrase'] != $body['confirmPassphrase']) {
            return new HttpError(400, 'Bad Request', 'Passphrases must be equal');
        }
    }

    return null;
}

/**
 * @param $models
 * @return HttpError|null
 */
function validatePostModels($models): ?HttpError {
    if (!isset($models)) {
        return new HttpError(400, 'Bad Request', 'Models are required');
    }
    if (!is_array($models)) {
        return new HttpError(400, 'Bad Request', 'Models must be an array');
    }
    if (count($models) == 0) {
        return new HttpError(400, 'Bad Request', 'Models must not be empty');
    }

    // foreach
    for ($i = 0; $i < count($models); $i++) {
        $error = validatePostModel($models[$i]);
        if ($error) {
            return new HttpError(400, 'Bad Request', 'Model ' . $i . ' is invalid: ' . $error->getDetails());
        }

    }

    return null;
}

/**
 * @param $model
 * @return HttpError|null
 */
function validatePostModel($model): ?HttpError {
    if (!isset($model)) {
        return new HttpError(400, 'Bad Request', 'Model is required');
    }
    if (!isset($model['name']) && !isset($model['id'])) {
        return new HttpError(400, 'Bad Request', 'Name or id is required');
    }
    if (isset($model['name']) && isset($model['id'])) {
        return new HttpError(400, 'Bad Request', 'Name and id is required');
    }

    return null;
}

/**
 * @param $type
 * @return HttpError|null
 */
function validatePostType($type): ?HttpError {
    if (!isset($type)) {
        return new HttpError(400, 'Bad Request', 'Type is required');
    }
    if (!isset($type['name']) && !isset($type['id'])) {
        return new HttpError(400, 'Bad Request', 'Name of id is required');
    }
    if (isset($type['name']) && isset($type['id'])) {
        return new HttpError(400, 'Bad Request', 'Name of id is required');
    }

    return null;
}

/**
 * @param $images
 * @return HttpError|null
 */
function validatePostImages($images): ?HttpError {
    if (!isset($images)) {
        return new HttpError(400, 'Bad Request', 'Images are required');
    }
    if (!is_array($images)) {
        return new HttpError(400, 'Bad Request', 'Images must be an array');
    }
    if (count($images) == 0) {
        return new HttpError(400, 'Bad Request', 'Images must not be empty');
    }

    // foreach
    for ($i = 0; $i < count($images); $i++) {
        $error = validatePostImage($images[$i]);
        if ($error) {
            return new HttpError(400, 'Bad Request', 'Image ' . $i . ' is invalid: ' . $error->getDetails());
        }

    }

    return null;
}

/**
 * @param $image
 * @return HttpError|null
 */
function validatePostImage($image): ?HttpError {
    if (!isset($image)) {
        return new HttpError(400, 'Bad Request', 'Image is required');
    }
    if (!isset($image['url'])) {
        return new HttpError(400, 'Bad Request', 'Url is required');
    }

    return null;
}

/**
 * @param $authorization
 * @param $body
 * @return HttpError|null
 */
function validatePostProduct($authorization, $body): ?HttpError {
    if (!isset($authorization)) {
        return new HttpError(401, 'Unauthorized', 'Authorization header is required');
    }
    $bearer = explode(" ", $authorization);

    if (count($bearer) < 2 || $bearer[0] != "Bearer") {
        return new HttpError(401, 'Unauthorized', 'Authorization header must be a Bearer token');
    }

    $jwt = $bearer[1];
    $jswDecoded = decodeJwT($jwt);

    if ($jswDecoded == null) {
        return new HttpError(401, 'Unauthorized', 'Invalid token');
    }

    if ($jswDecoded["role"] != "SELLER" && $jswDecoded["role"] != "ADMIN") {
        return new HttpError(401, 'Unauthorized', 'You cannot create a product');
    }

    if (!isset($body)) {
        return new HttpError(400, 'Bad Request', 'Body is required');
    }
    if (!isset($body['name'])) {
        return new HttpError(400, 'Bad Request', 'Name is required');
    }
    if (!isset($body['height'])) {
        return new HttpError(400, 'Bad Request', 'Height is required');
    }
    if (!isset($body['length'])) {
        return new HttpError(400, 'Bad Request', 'Length is required');
    }
    if (!isset($body['maxSpeed'])) {
        return new HttpError(400, 'Bad Request', 'Max speed is required');
    }
    if (!isset($body['capacity'])) {
        return new HttpError(400, 'Bad Request', 'Capacity is required');
    }
    if (!isset($body['price'])) {
        return new HttpError(400, 'Bad Request', 'Price is required');
    }
    if (!isset($body['type'])) {
        return new HttpError(400, 'Bad Request', 'Type is required');
    }
    $typeValidation = validatePostType($body['type']);
    if ($typeValidation) {
        return $typeValidation;
    }

    if (!isset($body['models'])) {
        return new HttpError(400, 'Bad Request', 'Models is required');
    }
    $modelsValidation = validatePostModels($body['models']);
    if ($modelsValidation) {
        return $modelsValidation;
    }

    if (!isset($body['images'])) {
        return new HttpError(400, 'Bad Request', 'Images is required');
    }
    $imagesValidation = validatePostImages($body['images']);
    if ($imagesValidation) {
        return $imagesValidation;
    }

    if ($jswDecoded["role"] == "ADMIN") {
        if (!isset($body['manufacturer'])) {
            return new HttpError(400, 'Bad Request', 'Manufacturer is required');
        }
        if (!isset($body['manufacturer']['id']) && !isset($body['manufacturer']['name'])) {
            return new HttpError(400, 'Bad Request', 'Manufacturer must contain id or name');
        }
        if (isset($body['manufacturer']['id']) && isset($body['manufacturer']['name'])) {
            return new HttpError(400, 'Bad Request', 'Manufacturer must contain id or name, not both');
        }
    }

    return null;
}

/**
 * @param $jwt
 * @param $id
 * @return HttpError|null
 */
function validatePatchProductToggle($jwt, $id): ?HttpError {
    if (!isset($jwt)) {
        return new HttpError(401, 'Unauthorized', 'Authorization header is required');
    }
    $bearer = explode(" ", $jwt);

    if (count($bearer) < 2 || $bearer[0] != "Bearer") {
        return new HttpError(401, 'Unauthorized', 'Authorization header must be a Bearer token');
    }

    $jwt = $bearer[1];
    $jswDecoded = decodeJwT($jwt);

    if ($jswDecoded == null) {
        return new HttpError(401, 'Unauthorized', 'Invalid token');
    }
    if ($jswDecoded["role"] != "SELLER" && $jswDecoded["role"] != "ADMIN") {
        return new HttpError(401, 'Unauthorized', 'You cannot toggle a product');
    }
    if (!isset($id)) {
        return new HttpError(400, 'Bad Request', 'Id is required');
    }

    return null;
}

function validateGetManufacturerProducts($jwt, $id) : ?HttpError {
    if (!isset($jwt)) {
        return new HttpError(401, 'Unauthorized', 'Authorization header is required');
    }
    $bearer = explode(" ", $jwt);

    if (count($bearer) < 2 || $bearer[0] != "Bearer") {
        return new HttpError(401, 'Unauthorized', 'Authorization header must be a Bearer token');
    }

    $jwt = $bearer[1];
    $jswDecoded = decodeJwT($jwt);

    if ($jswDecoded == null) {
        return new HttpError(401, 'Unauthorized', 'Invalid token');
    }
    if ($jswDecoded["role"] != "SELLER" && $jswDecoded["role"] != "ADMIN") {
        return new HttpError(401, 'Unauthorized', 'You cannot toggle a product');
    }
    if (!isset($id)) {
        return new HttpError(400, 'Bad Request', 'Id is required');
    }

    return null;
}