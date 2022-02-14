<?php
// src/Controller/LuckyController.php
namespace App\Controller;

use Symfony\Component\HttpClient\HttpClient;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

class TestController extends AbstractController
{

    /**
     * @Route("/")
     * @Route("/lucky/numbero")
     */
    public function index()
    {
        $request    = Request::createFromGlobals();
        $operation  = $request->request->get('operation', null);

        //requested data
        $valueA     = null;
        $valueB     = null;
        $expression = null;

        // result variables
        $result     = null;
        $error      = null;

        // ASP.Net API Info
        $apiUri = 'https://localhost:7024/Operator/';

        if (isset($operation)) {
            // Check if client request contains required data
            if ($operation == 'regex') {
                $expression = $request->request->get('expression', null);

                if (empty($expression)) {
                    $error = true;
                    $result = "An arithmetical operation must to be defined.";
                }
            } else {
                $valueA = $request->request->get('valueA', null);
                $valueB = $request->request->get('valueB', null);
                
                if (empty($valueA)) {
                    $error = true;
                    $result = 'Value A must to be defined.';
                }

                if ($operation != 'fac' && empty($valueB)) {
                    $error = true;
                    $result = ($error ? "{$result}\n" : '') . 'Value B must to be defined.';
                }
            }

            // If all data is valid send API request
            if (!isset($error)) {
                switch ($operation) {
                    case 'add':
                        $apiRequest = "Sumar/{$valueA}/{$valueB}";
                        break;
                    case 'sub':
                        $apiRequest = "Restar/{$valueA}/{$valueB}";
                        break;
                    case 'mul':
                        $apiRequest = "Multiplicar/{$valueA}/{$valueB}";
                        break;
                    case 'div':
                        $apiRequest = "Dividir/{$valueA}/{$valueB}";
                        break;
                    case 'fac':
                        $apiRequest = "Factorial?Value={$valueA}";
                        break;
                    default:
                        $apiRequest = 'CalcularExpresion?Value=' . urlencode($expression);
                        break;
                }

                $client = HttpClient::create();
                try {
                    $response = $client->request('GET', "{$apiUri}{$apiRequest}");

                } catch (Exception $e) {
                    $error = true;
                    $result = $e->getMessage();
                }

                if (!$error) {
                    $statusCode = $response->getStatusCode();

                    if ($statusCode != 200) {
                        $error = true;
                        $result = "Server Status Code Was {$statusCode}";
                    }

                    $result = $response->getContent();
                }
            }
        }

        return $this->render('test/index.html.twig', [
            'operation' => $operation,
            'valueA'    => $valueA,
            'valueB'    => $valueB,
            'expression'=> $expression,
            'result'    => $result,
            'error'     => $error,
        ]);
    }
}

