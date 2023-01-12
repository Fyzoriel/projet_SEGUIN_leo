<?php
header('Access-Control-Allow-Origin: *');
header("Access-Control-Allow-Methods: HEAD, GET, POST, PUT, PATCH, DELETE, OPTIONS");
header("Access-Control-Allow-Headers: Origin, X-Requested-With, Content-Type, Accept, Access-Control-Request-Method, Access-Control-Request-Headers, Authorization, test");
header('Content-Type: application/json');
header("HTTP/1.1 200 OK");
$method = $_SERVER['REQUEST_METHOD'];
if ($method == "OPTIONS") {
    header('Access-Control-Allow-Origin: *');
    header("Access-Control-Allow-Headers: Origin, X-Requested-With, Content-Type, Accept, Access-Control-Request-Method, Access-Control-Request-Headers, Authorization, test");
    header("HTTP/1.1 200 OK");
    die();
}

use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use Slim\Factory\AppFactory;
use Tuupola\Middleware\HttpBasicAuthentication;
use \Firebase\JWT\JWT;

require __DIR__ . '/../vendor/autoload.php';
require_once __DIR__ . '/../bootstrap.php';

include_once './utils.php';
include_once './storage.php';

include_once './models/Addresses.php';
include_once './models/Images.php';
include_once './models/Manufacturers.php';
include_once './models/Models.php';
include_once './models/Products.php';
include_once './models/Types.php';
include_once './models/Users.php';

include_once './validators.php';

$app = AppFactory::create();

/*
 * Auth
 */
$app->post('/api/auth/login', function (Request $request, Response $response) {
    global $entityManager;
    $userRepository = $entityManager->getRepository(Users::class);

    // Thanks, Fanny, for the following line of code that I had to copy/paste from your project - need this hack because the framework doesn't work...
    $inputJSON = file_get_contents('php://input');
    $body = json_decode($inputJSON, TRUE);

    $error = validatePostLogin($body);
    if ($error) {
        return error($response, $error);
    }

    $email = $body['email'];
    $pass = $body['passphrase'];

    $user = $userRepository->findOneBy(array('email' => $email));

    if ($user == null) {
        return error($response, new HttpError(401, "Unauthorized", "Invalid email or password"));
    }

    if (!password_verify($pass, $user->getPassphrase())) {
        return error($response, new HttpError(401, "Unauthorized", "Invalid email or password"));
    }

    // If user not validated
    if (($user->getRole() == "SELLER" || $user->getRole() == "ADMIN") && !$user->getValidated()) {
        return error($response, new HttpError(401, "Unauthorized", "User not validated"));
    }

    $manufacturer = $user->getManufacturer();
    $manufacturerId = null;
    if ($manufacturer) {
        $manufacturerId = $manufacturer->getId();
    }

    $jwt = createJwT($user->getId(), $user->getRole(), $manufacturerId);
    $response = $response->withHeader("Authorization", "Bearer {$jwt}");
    $response->withStatus(200);

    return withHeader($response);
});

$app->post('/api/auth/register', function (Request $request, Response $response) {
    global $entityManager;
    $userRepository = $entityManager->getRepository(Users::class);
    $ManufacturerRepository = $entityManager->getRepository(Manufacturers::class);

    $inputJSON = file_get_contents('php://input');
    $body = json_decode($inputJSON, TRUE);

    $error = validatePostRegister($body);
    if ($error) {
        return error($response, $error);
    }

    $firstName = $body['firstName'];
    $name = $body['name'];
    $email = $body['email'];
    $phone = $body['phone'];
    $addresses = $body['addresses'];
    $passphrase = $body['passphrase'];
    $role = $body['role'];
    $manufacturerBody = $body['manufacturer'];

    $user = $userRepository->findOneBy(array('email' => $email));
    if ($user) {
        return error($response,
            new HttpError(409, 'Conflict', 'User already exists')
        );
    }
    if ($role == 'SELLER') {
        if ($manufacturerBody['name']) {
            // Create manufacturer with name
            $manufacturer = new Manufacturers();
            $manufacturer->setName($manufacturerBody['name']);
            $entityManager->persist($manufacturer);
        }
        else {
            // Get manufacturer by id
            $manufacturer = $ManufacturerRepository->findOneById($manufacturerBody['id']);
            if ($manufacturer == null) {
                return error($response,
                    new HttpError(404, 'Not Found', 'Manufacturer not found')
                );
            }
        }
    }

    $user = new Users();
    $user->setFirstName($firstName);
    $user->setName($name);
    $user->setEmail($email);
    $user->setPhone($phone);
    $user->setPassphrase(password_hash($passphrase, PASSWORD_DEFAULT));
    $user->setRole($role);
    $user->setManufacturer($manufacturer ?? null);
    $user->setValidated($role == "CLIENT");
    $entityManager->persist($user);

    // for each address, create and persist it in the database

    if (isset($addresses)){
        foreach ($addresses as $addressBody) {
            $address = new Addresses();
            $address->setState($addressBody['state']);
            $address->setZip($addressBody['zip']);
            $address->setCity($addressBody['city']);
            $address->setStreet($addressBody['street']);
            $address->setStreetNumber($addressBody['streetNumber']);
            $address->setType($addressBody['type']);
            $address->setName($addressBody['name']);
            $address->setUser($user);

            $entityManager->persist($address);
        }
    }

    $entityManager->flush();

    $response = $response->withStatus(201);
    return withHeader($response);
});

/*
 * user
 */
$app->get('/api/user/{id}', function (Request $request, Response $response, $args) {
    global $entityManager;
    $userRepository = $entityManager->getRepository(Users::class);

    $id = $args['id'];

    // Php method because the framework doesn't work even with Access-Control-Expose-Headers... ... ... ... FML
    $headers = getallheaders();
    $authorization = $headers["Authorization"];

    $error = validateGetUser($authorization, $id);

    if ($error) {
        return error($response, $error);
    }

    $user = $userRepository->findOneById($id);

    if (!$user) {
        return error($response, new HttpError(404, "Not Found", "User not found"));
    }

    $userArray = userToArray($user);
    
    $response = $response->withStatus(200);
    $response->getBody()->write(json_encode($userArray));

    return withHeader($response);
});

$app->patch('/api/user/{id}', function (Request $request, Response $response, $args) {
    global $entityManager;
    $userRepository = $entityManager->getRepository(Users::class);

    $inputJSON = file_get_contents('php://input');
    $body = json_decode($inputJSON, TRUE);

    $id = $args['id'];

    // Php method because the framework doesn't work even with Access-Control-Expose-Headers... ... ... ... FML
    $headers = getallheaders();
    $authorization = $headers["Authorization"];

    $error = validatePatchUser($authorization, $id, $body);
    if ($error != null) {
        return error($response, $error);
    }

    $bearer = explode(" ", $authorization);
    $jwt = $bearer[1];
    $jswDecoded = decodeJwT($jwt);

    $userid = $jswDecoded["userid"];

    $user = $userRepository->findOneById($userid);

    if (!$user) {
        return error($response, new HttpError(404, "Not Found", "User not found"));
    }

    $error = validatePatchUserBody($body, $user->getRole());
    if ($error != null) {
        return error($response, $error);
    }

    $passphrase = $body['passphrase'];
    $oldPassphrase = $body['oldPassphrase'];
    if ($passphrase != null && $oldPassphrase != null) {
        if (!password_verify($oldPassphrase, $user->getPassphrase())) {
            return error($response, new HttpError(401, "Unauthorized", "Invalid old password"));
        }
        $user->setPassphrase(password_hash($passphrase, PASSWORD_DEFAULT));
    }

    // Update user
    $user->setFirstName($body['firstName']);
    $user->setName($body['name']);
    $user->setEmail($body['email']);

    if ($user->getRole() == "CLIENT") {
        $user->setPhone($body['phone']);

        // Compare each address in addresses with the body,
        // if address it found then update it,
        // if address is in addresses but not in body then delete it,
        // if address is in body but not in addresses then create it
        $addresses = $user->getAddresses();
        foreach ($addresses as $address) {
            $found = false;
            foreach ($body['addresses'] as $addressBody) {
                if ($address->getId() == $addressBody['id']) {
                    $found = true;
                    $address->setState($addressBody['state']);
                    $address->setZip($addressBody['zip']);
                    $address->setCity($addressBody['city']);
                    $address->setStreet($addressBody['street']);
                    $address->setStreetNumber($addressBody['streetNumber']);
                    $address->setType($addressBody['type']);
                    $address->setName($addressBody['name']);
                    $address->setUser($user);
                    $entityManager->persist($address);
                }
            }
            if (!$found) {
                $entityManager->remove($address);
            }
        }

        // create new addresses
        foreach ($body['addresses'] as $addressBody) {
            if ($addressBody['id'] == null) {
                $address = new Addresses();
                $address->setState($addressBody['state']);
                $address->setZip($addressBody['zip']);
                $address->setCity($addressBody['city']);
                $address->setStreet($addressBody['street']);
                $address->setStreetNumber($addressBody['streetNumber']);
                $address->setType($addressBody['type']);
                $address->setUser($user);
                $address->setName($addressBody['name']);
                $entityManager->persist($address);
            }
        }
    }
    $entityManager->persist($user);
    $entityManager->flush();

    $userArray = userToArray($user);

    $response = $response->withStatus(200);
    $response->getBody()->write(json_encode($userArray));
    return withHeader($response);
});

/*
 * Product
 */
$app->get('/api/product', function (Request $request, Response $response, $args) {
    global $entityManager;

    // getQueryParams
    $query = $request->getQueryParams();
    $name = $query['name'] ?? "";
    $models = $query['models'] ?? null;

    $qb = $entityManager->createQueryBuilder();
    if ($models) {
        if (!is_array($models)) {
            $models = array($models);
        }

        $sub = $entityManager->createQueryBuilder();

        $sub->select('p')
            ->from('Products', 'p')
            ->join('p.model', 'm')
            ->where($qb->expr()->in('m.id', $models))
            ->groupBy('p.id')
            ->having('count(p.id) = :count')
            ->setParameter('count', count($models));
        $subquery = $sub->getQuery();
        $productsSub = $subquery->getResult();

        $productsIds = array();
        foreach ($productsSub as $productSub) {
            $productsIds[] = $productSub->getId();
        }

        // if $productsIds empty return empty json
        if (empty($productsIds)) {
            $response = $response->withStatus(200);
            $response->getBody()->write(json_encode(array()));
            return withHeader($response);
        }

        $qb->select('p', 't', 'i', 'mdl', 'm')
            ->from('Products', 'p')
            ->innerJoin('p.manufacturer', 'm')
            ->innerJoin('p.type', 't')
            ->innerJoin('p.images', 'i')
            ->innerJoin('p.model', 'mdl')
            ->where('p.enabled = true')
            ->andWhere('m.validated = true')
            ->andWhere('p.name LIKE :value')
            ->andWhere($qb->expr()->in('p.id', $productsIds))
            ->setParameter('value', '%' . $name . '%')
            ->groupBy('p.id', 't.id', 'i.id', 'mdl.id', 'm.id')
            ->orderBy('p.id', "ASC");
    } else {
        $qb->select('p', 't', 'i', 'mdl', 'm')
            ->from('Products', 'p')
            ->innerJoin('p.manufacturer', 'm')
            ->innerJoin('p.type', 't')
            ->innerJoin('p.model', 'mdl')
            ->innerJoin('p.images', 'i')
            ->where('p.enabled = true')
            ->andWhere('m.validated = true')
            ->andWhere('p.name LIKE :value')
            ->setParameter('value', '%' . $name . '%')
            ->groupBy('p.id, t.id, mdl.id, m.id, i.id')
            ->orderBy('p.id', "ASC");
    }

    $query = $qb->getQuery();
    $products = $query->getResult();

    $productsArray = productsToArray($products);

    $response = $response->withStatus(200);
    $response->getBody()->write(json_encode($productsArray));
    return withHeader($response);
});

$app->get('/api/product/{id}', function (Request $request, Response $response, $args) {
    global $entityManager;
    $productRepository = $entityManager->getRepository(Products::class);

    $id = $args['id'];
    if (!isset($id)) {
        return error($response, new HttpError(400, 'Bad Request', 'Id is required'));
    }


    $product = $productRepository->findOneById($id);
    if (!$product) {
        return error($response, new HttpError(404, "Not Found", "Product not found"));
    }

    $productArray = productToArray($product);

    $response = $response->withStatus(200);
    $response->getBody()->write(json_encode($productArray));

    return withHeader($response);
});

$app->post('/api/product', function (Request $request, Response $response, $args) {
    global $entityManager;
    $userRepository = $entityManager->getRepository(Users::class);
    $typeRepository = $entityManager->getRepository(Types::class);
    $manufacturerRepository = $entityManager->getRepository(Manufacturers::class);
    $modelRepository = $entityManager->getRepository(Models::class);

    $inputJSON = file_get_contents('php://input');
    $body = json_decode($inputJSON, TRUE);

    $headers = getallheaders();
    $authorization = $headers["Authorization"];

    $error = validatePostProduct($authorization, $body);
    if ($error != null) {
        return error($response, $error);
    }

    $bearer = explode(" ", $authorization);
    $jwt = $bearer[1];
    $jswDecoded = decodeJwT($jwt);

    $userid = $jswDecoded["userid"];
    $role = $jswDecoded["role"];

    $name = $body['name'];
    $height = $body['height'];
    $length = $body['length'];
    $maxSpeed = $body['maxSpeed'];
    $capacity = $body['capacity'];
    $price = $body['price'];
    $typeBody =  $body['type'];
    $manufacturerBody = $body['manufacturer'];
    $images = $body['images'];

    // Find or create type
    if ($typeBody['id']){
        $type = $typeRepository->findOneBy(array('id' => $typeBody['id']));
        if (!$type) {
            return error($response, new HttpError(404, "Not Found", "Type not found"));
        }
    } else {
        // Create new type
        $type = new Types();
        $type->setName($typeBody['name']);
        $entityManager->persist($type);
    }

    if ($role == "SELLER") {
        $user = $userRepository->findOneBy(array('id' => $userid));
        if (!$user) {
            return error($response, new HttpError(404, "Not Found", "User not found"));
        }
        $manufacturer = $user->getManufacturer();
    } else {
        if ($manufacturerBody['id']){
            $manufacturer = $manufacturerRepository->findOneBy(array('id' => $manufacturerBody['id']));
            if (!$manufacturer) {
                return error($response, new HttpError(404, "Not Found", "Manufacturer not found"));
            }
        } else {
            $manufacturer = new Manufacturers();
            $manufacturer->setName($manufacturerBody['name']);
            $entityManager->persist($manufacturer);
        }
    }

    $product = new Products();
    $product->setName($name);
    $product->setHeight($height);
    $product->setLength($length);
    $product->setMaxSpeed($maxSpeed);
    $product->setCapacity($capacity);
    $product->setPrice($price);
    $product->setType($type);
    $product->setManufacturer($manufacturer);

    // Create each images
    foreach ($images as $imageBody) {
        $image = new Images();
        $image->setUrl($imageBody['url']);
        $image->setProduct($product);
        $entityManager->persist($image);
    }

    // Find or create models
    foreach ($body['models'] as $modelBody) {
        if ($modelBody['id']) {
            $model = $modelRepository->findOneBy(array('id' => $modelBody['id']));
            if (!$model) {
                return error($response, new HttpError(404, "Not Found", "Model not found"));
            }
        } else {
            $model = new Models();
            $model->setName($modelBody['name']);
        }

        $model->addProduct($product);
        $entityManager->persist($model);
        $product->addModel($model);
    }

    $entityManager->persist($product);
    $entityManager->flush();

    $productArray = productToArray($product);

    $response = $response->withStatus(201);
    $response->getBody()->write(json_encode($productArray));

    return withHeader($response);
});

$app->patch('/api/product/{id}/toggle', function (Request $request, Response $response, $args) {
    global $entityManager;
    $userRepository = $entityManager->getRepository(Users::class);
    $productRepository = $entityManager->getRepository(Products::class);

    $headers = getallheaders();
    $id = $args['id'];

    $authorization = $headers["Authorization"];
    $error = validatePatchProductToggle($authorization, $id);
    if ($error) {
        return error($response, $error);
    }

    $bearer = explode(" ", $authorization);
    $jwt = $bearer[1];
    $jswDecoded = decodeJwT($jwt);

    $userid = $jswDecoded["userid"];
    $role = $jswDecoded["role"];

    $product = $productRepository->findOneById($id);
    if (!$product) {
        return error($response, new HttpError(404, "Not Found", "Product not found"));
    }

    if ($role == "SELLER") {
        $user = $userRepository->findOneById($userid);
        if (!$user) {
            return error($response, new HttpError(404, "Not Found", "User not found"));
        }
        $manufacturer = $user->getManufacturer();

        if ($product->getManufacturer()->getId() != $manufacturer->getId()) {
            return error($response, new HttpError(403, "Forbidden", "You are not allowed to toggle this product"));
        }
    }

    $product->setEnabled(!$product->getEnabled());
    $entityManager->persist($product);
    $entityManager->flush();

    $productArray = productToArray($product);

    $response = $response->withStatus(200);
    $response->getBody()->write(json_encode($productArray));
    return withHeader($response);
});

$app->get('/api/model', function (Request $request, Response $response, $args) {
    global $entityManager;
    $modelRepository = $entityManager->getRepository(Models::class);

    $models = $modelRepository->findAll();

    $modelsArray = modelsToArray($models);

    $response = $response->withStatus(200);
    $response->getBody()->write(json_encode($modelsArray));

    return withHeader($response);
});

$app->get('/api/manufacturer',function (Request $request, Response $response, $args){
    global $entityManager;
    $manufacturerRepository = $entityManager->getRepository(Manufacturers::class);

    $manufacturers = $manufacturerRepository->findAll();

    $manufacturersArray = array();
    foreach ($manufacturers as $manufacturer) {
        $manufacturersArray[] = $manufacturer;
    }

    $manufacturersArray = manufacturersToArray($manufacturersArray);

    $response = $response->withStatus(200);
    $response->getBody()->write(json_encode($manufacturersArray));

    return withHeader($response);
});

$app->get('/api/manufacturer/{id}/products', function (Request $request, Response $response, $args){
    global $entityManager;
    $manufacturerRepository = $entityManager->getRepository(Manufacturers::class);

    $headers = getallheaders();
    $id = $args['id'];

    $authorization = $headers["Authorization"];
    $error = validateGetManufacturerProducts($authorization, $id);
    if ($error) {
        return error($response, $error);
    }

    $manufacturer = $manufacturerRepository->findOneById($id);

    if (!$manufacturer) {
        return error($response, new HttpError(404, "Not Found", "Manufacturer not found"));
    }

    $productsArray = array();
    foreach ($manufacturer->getProducts() as $product) {
        $productsArray[] = $product;
    }

    $productsArray = productsToArray($productsArray);

    $response = $response->withStatus(200);
    $response->getBody()->write(json_encode($productsArray));
    return withHeader($response);
});

$options = [
    "attribute" => "token",
    "header" => "Authorization",
    "regexp" => "/Bearer\s+(.*)$/i",
    "secure" => false,
    "algorithm" => ["HS256"],
    "secret" => JWT_SECRET,
    "path" => ["/api"],
    "rules" => [
        new Tuupola\Middleware\JwtAuthentication\RequestMethodRule([
            "ignore" => ["GET"],
            "path" => ["/api/model", "/api/product"],
        ]),
        new Tuupola\Middleware\JwtAuthentication\RequestMethodRule([
            "ignore" => ["POST"],
            "path" => ["/api/auth/login", "/api/auth/register"],
        ])
    ],
    "error" => function ($response, $arguments) {
        $response = error($response, new HttpError(401, "Unauthorized", "Jwt token is invalid"));
        return withHeader($response);
    }
];

$app->add(new Tuupola\Middleware\JwtAuthentication($options));
$app->add(new Tuupola\Middleware\CorsMiddleware([
    "origin" => ["*"],
    "methods" => ["GET", "POST", "PUT", "PATCH", "DELETE"],
    "headers.allow" => ["Origin", "X-Requested-With", "Content-Type", "Accept", "Access-Control-Request-Method", "Access-Control-Request-Headers", "Authorization", "test"],
    "headers.expose" => ["Authorization"],
]));
$app->run();