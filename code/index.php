<?php declare(strict_types=1);

require_once 'vendor/autoload.php';

use App\DBConnector\DBConnector;
use App\Http\ResponseFactory;
use App\Http\Router;
use App\Pkg\Animal\AnimalController;
use App\Pkg\Animal\AnimalRepository;
use App\Pkg\Animal\AnimalService;
use App\Pkg\Animal\AnimalValidator;
use App\Pkg\Customer\CustomerController;
use App\Pkg\Customer\CustomerRepository;
use App\Pkg\Customer\CustomerService;
use App\Pkg\Customer\CustomerValidator;
use Symfony\Component\HttpFoundation\Request;

try {
    $mysqlConnection   = DBConnector::getMySqlConnection();
    $mariadbConnection = DBConnector::getMariaDBConnection();
    $pgConnection      = DBConnector::getPostgresDbConnection();

    // Poor Man's Dependency Injection
    // ToDo: instead of instantiating everything, we could build a simple IoC container that returns lambda-functions to
    //       instantiate the controllers after deciding which controller to use based on the route. This will help
    //       us avoid instantiating controllers that are not needed (including their dependencies).
    //       The result should be a faster bootstrap and a lower memory footprint.
    //       This would require us to have a central route-list or that the routes can be accessed statically in all Controllers.
    //       🤷 Maybe some day...

    $animalRepo       = new AnimalRepository($mysqlConnection);
    $animalValidator  = new AnimalValidator();
    $animalService    = new AnimalService($animalRepo, $animalValidator);
    $animalController = new AnimalController($animalService);

    $customerRepo       = new CustomerRepository($mariadbConnection);
    $customerValidator  = new CustomerValidator();
    $customerService    = new CustomerService($customerRepo, $customerValidator);
    $customerController = new CustomerController($customerService, $animalService);

    $router = new Router($animalController, $customerController);
    $router->handle($request = Request::createFromGlobals())->send();
    exit(0);
} catch (Throwable $e) {
    ResponseFactory::error($e)->send();
    exit(1);
}



