<?php
use Psr\Http\Message\ResponseInterface as Response;
use \Firebase\JWT\JWT;

include_once "./HttpError.php";

function error(Response $response, $HttpError) {
    $response = $response->withStatus($HttpError->getCode());
    $response->getBody()->write(json_encode(Array("error" => $HttpError->getMessage(), "details" => $HttpError->getDetails())));
    return $response;
}

function withHeader(Response $response): Response {
    return $response
        ->withHeader("Content-Type", "application/json")
        ->withHeader("Access-Control-Allow-Origin", "*")
        ->withHeader("Access-Control-Allow-Headers", "Origin", "X-Requested-With", "Content-Type", "Accept", "Access-Control-Request-Method", "Access-Control-Request-Headers", "Authorization", "test")
        ->withHeader("Access-Control-Allow-Methods", "GET, POST, PUT, DELETE, OPTIONS")
        ->withHeader("Access-Control-Expose-Headers", "Authorization");
}

const JWT_SECRET = "ezrdfyughirojtpkgh";

function PHP_CEST_DE_LA_MERDE_JE_VEUX_CANNER_ERREURS_A_LA_CON_PARCE_QUE_RIEN_N_EST_LOGIQUE_DANS_CE_TRUC($var): bool
{
    return isset($var);
}

function createJwT(int $userId, string $role, int $manufacturerId = null): string {
    $issuedAt = time();
    $expirationTime = $issuedAt + 600000;
    $payload = array(
        "userid" => $userId,
        "role" => $role,
        "manufacturerId" => $manufacturerId,
        "iat" => $issuedAt,
        "exp" => $expirationTime
    );

    return JWT::encode($payload, JWT_SECRET, "HS256");
}

function decodeJwT(string $token): array {
    return (array) JWT::decode($token, JWT_SECRET, array("HS256"));
}

function usersToArray(array $users): array {
    $userArray = array();
    foreach ($users as $user) {
        $userArray[] = userToArray($user);
    }
    return array_filter($userArray);
}
function userToArray(Users $user): array {
    $addresses = $user->getAddresses();
    $addressesResponse = array();
    foreach ($addresses as $address) {
        $addressesResponse[] = array(
            "id" => $address->getId(),
            "state" => $address->getState(),
            "zip" => $address->getZip(),
            "city" => $address->getCity(),
            "street" => $address->getStreet(),
            "streetNumber" => $address->getStreetNumber(),
            "type" => $address->getType(),
            "name" => $address->getName()
        );
    }

    $manufacturer = $user->getManufacturer();
    if ($manufacturer) {
        $manufacturerResponse = array(
            "id" => $manufacturer->getId(),
            "name" => $manufacturer->getName(),
        );
    } else {
        $manufacturerResponse = null;
    }

    return array_filter(array(
        "id" => $user->getId(),
        "firstName" => $user->getFirstName(),
        "name" => $user->getName(),
        "email" => $user->getEmail(),
        "phone" => $user->getPhone(),
        "role" => $user->getRole(),
        "addresses" => $addressesResponse,
        "manufacturer" => $manufacturerResponse
    ));
}

function productsToArray(array $products): array {
    $productsArray = [];
    foreach ($products as $product) {
        $productsArray[] = productToArray($product);
    }
    return array_filter($productsArray);
}
function productToArray(Products $product): array {
    $manufacturer = $product->getManufacturer();
    $type = $product->getType();

    $images = $product->getImages();
    $imagesResponse = array();
    foreach ($images as $image) {
        $imagesResponse[] = array(
            "id" => $image->getId(),
            "url" => $image->getUrl()
        );
    }
    $models = $product->getModel();
    $modelsResponse = array();
    foreach ($models as $model) {
        $modelsResponse[] = array(
            "id" => $model->getId(),
            "name" => $model->getName()
        );
    }

    return array_filter(array(
        "id" => $product->getId(),
        "name" => $product->getName(),
        "height" => $product->getHeight(),
        "length" => $product->getLength(),
        "maxSpeed" => $product->getMaxSpeed(),
        "capacity" => $product->getCapacity(),
        "price" => $product->getPrice(),
        "enable" => $product->getEnabled(),
        "manufacturer" => array(
            "id" => $manufacturer->getId(),
            "name" => $manufacturer->getName()
        ),
        "images" => $imagesResponse,
        "type" => array(
            "id" => $type->getId(),
            "name" => $type->getName()
        ),
        "models" => $modelsResponse,
    ), 'PHP_CEST_DE_LA_MERDE_JE_VEUX_CANNER_ERREURS_A_LA_CON_PARCE_QUE_RIEN_N_EST_LOGIQUE_DANS_CE_TRUC');
}

function modelsToArray(array $models): array {
    $modelsArray = [];
    foreach ($models as $model) {
        $modelsArray[] = modelToArray($model);
    }
    return array_filter($modelsArray);
}
function modelToArray(Models $models): array {
    return array_filter(array(
        "id" => $models->getId(),
        "name" => $models->getName()
    ));
}

function manufacturersToArray(array $manufacturers): array
{
    $manufacturersArray = [];
    foreach ($manufacturers as $manufacturer) {
        $manufacturersArray[] = manufacturerToArray($manufacturer);
    }
    return array_filter($manufacturersArray);
}

function manufacturerToArray($manufacturer): array
{
    return array_filter(array(
        "id" => $manufacturer->getId(),
        "name" => $manufacturer->getName()
    ));
}

