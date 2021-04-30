<?php
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use PsrJwt\Factory\Jwt;
use PsrJwt\Factory\JwtMiddleware;
use Slim\Routing\RouteCollectorProxy;
$db = null;
require_once __DIR__ . '/../encryption/Bcrypt.php';

$factory = new Jwt();
$builder = $factory->builder();

$dotenv = Dotenv\Dotenv::createImmutable(__DIR__ . "/../");
$dotenv->load();
$secret = $_ENV['JWT_SECRET'];


$app->group('/api', function (RouteCollectorProxy $group) use ($db, $secret, $builder, $app) {
	$group->group('/event', function (RouteCollectorProxy $group) use ($db, $secret, $builder, $app) {
		//Get All Events by user
		$group->get('/user/{id}/get', function (Request $request, Response $response, $args) use ($builder, $secret, $db) {
			require __DIR__ . '/../db/dbconnect.php';
			$id_user = $request->getAttribute("id");
			$result = $db->events()->where("user_id",$id_user);
			$response->getBody()->write(json_encode($result));
			return $response;
		})->add(JwtMiddleware::json($secret, 'jwt', ['Authorisation Failed']));
		//Get An Event by id
		$group->get('/get/{id}', function (Request $request, Response $response, $args) use ($builder, $secret, $db) {
			require __DIR__ . '/../db/dbconnect.php';
			$id = $request->getAttribute("id");
			$result = $db->events[$id];
			$response->getBody()->write(json_encode($result));
			return $response;
		});
		//Get All Events
		$group->post('/get', function (Request $request, Response $response, $args) use ($builder, $secret, $db) {
			require __DIR__ . '/../db/dbconnect.php';
			$post = $request->getParsedBody();
			$payload = json_decode(base64_decode($post['payload']),true);
			$c0 = $payload['0'];
			$c1 = $payload['1'];
			$c2 = $payload['2'];
			$c3 = $payload['3'];
			$c4 = $payload['4'];

			$data = [];

			if($c0){
				$result1 = $db->events()->where('type',0);
				foreach ($result1 as $item){
					$data[] = $item;
					$item['status'] = $item->status;
				}
			}

			if($c1){
				$result2 = $db->events()->where('type',1);
				foreach ($result2 as $item){
					$data[] = $item;
					$item['status'] = $item->status;
				}
			}

			if($c2){
				$result3 = $db->events()->where('type',2);
				foreach ($result3 as $item){
					$data[] = $item;
					$item['status'] = $item->status;
				}
			}

			if($c3){
				$result4 = $db->events()->where('type',3);
				foreach ($result4 as $item){
					$data[] = $item;
					$item['status'] = $item->status;
				}
			}

			if($c4){
				$result5 = $db->events()->where('type',4);
				foreach ($result5 as $item){
					$data[] = $item;
					$item['status'] = $item->status;
				}
			}

			$response->getBody()->write(json_encode($data));
			return $response;
		});
		//Create new Events
		$group->post('/insert', function (Request $request, Response $response, $args) use ($builder, $secret, $db) {
			require __DIR__ . '/../db/dbconnect.php';
			$post = $request->getParsedBody();
			$payload = json_decode(base64_decode($post['payload']),true);
			$user_id = $payload['user_id'];
			$location = $payload['location'];
			$latitude = $payload['latitude'];
			$longitude = $payload['longitude'];
			$photo = $payload['photo'];
			$description = $payload['description'];
			$type = $payload['type'];

			$data = array (
				"photo" => $photo,
				"user_id" => $user_id,
				"location" => $location,
				"latitude" => $latitude,
				"longitude" => $longitude,
				"description" => $description,
				"status_id" => 4,
				"date" => date("d/m/Y"),
				"time" => date("h:i"),
				"type" => $type
			);
			$result = $db->events()->insert($data);
			$response->getBody()->write(json_encode($result));
			return $response;
		})->add(JwtMiddleware::json($secret, 'jwt', ['Authorisation Failed']));
		//Delete Event by id
		$group->post('/delete', function (Request $request, Response $response, $args) use ($builder, $secret, $db) {
			require __DIR__ . '/../db/dbconnect.php';
			$post = $request->getParsedBody();
			$id = $post['id'];
			$user_id = $post['user_id'];
			$result = $db->events()->where("user_id",$user_id)->where('id',$id)->limit(1)->fetch();
			$result->delete();
			$response->getBody()->write(json_encode($result));
			return $response;
		})->add(JwtMiddleware::json($secret, 'jwt', ['Authorisation Failed']));
		//Update Event
		$group->post('/update', function (Request $request, Response $response, $args) use ($builder, $secret, $db) {
			require __DIR__ . '/../db/dbconnect.php';

			$post = $request->getParsedBody();
			$payload = json_decode(base64_decode($post['payload']),true);

			$id = $payload['id'];
			$description = $payload['description'];
			$type = $payload['type'];
			$user_id = $payload['user_id'];
			$data = array (
				"id" => $id,
				"type" => $type,
				"description" => $description,
			);
			$result = $db->events()->where("id",$id)->where("user_id",$user_id)->limit(1)->fetch();
			$result->update($data);
			if($result){
				$result['status'] = $result['status_id'];
				$result['type'] = $data['type'];
				$result['description'] = $data['description'];
			}
			$response->getBody()->write(json_encode($result));
			return $response;
		})->add(JwtMiddleware::json($secret, 'jwt', ['Authorisation Failed']));

	});
});