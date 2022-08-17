<?php


namespace App\Controllers;

use CodeIgniter\Controller;
use CodeIgniter\HTTP\CLIRequest;
use CodeIgniter\HTTP\IncomingRequest;
use CodeIgniter\HTTP\RequestInterface;
use CodeIgniter\HTTP\ResponseInterface;
use Psr\Log\LoggerInterface;

/**
 * Class BaseController
 *
 * BaseController provides a convenient place for loading components
 * and performing functions that are needed by all your controllers.
 * Extend this class in any new controllers:
 *     class Home extends BaseController
 *
 * For security be sure to declare any new methods as protected or private.
 */
class CodeReviewController extends BaseController
{
    private $func_exclude = array('__construct', 'initController', '');
    private $file_exclude = array('code_review.php');

    public function index()
    {
        ini_set('memory_limit', '2048M');
        $controller_files = array();
        $model_files = array();
        $view_files = array();

        $controller_files = $this->list_functions_in_files('Controllers/', $this->list_files_in_folder('Controllers'), $controller_files);
        $model_files = $this->list_functions_in_files('Models/', $this->list_files_in_folder('Models'), $model_files);
        $view_files = $this->list_functions_in_files('Views/', $this->list_files_in_folder('Views'), $view_files);

        $merged_files = array_merge($controller_files, $model_files);
        $model_files = $this->function_usage_in_group_of_files($model_files, $merged_files, 'model');

        $controller_files = $this->function_usage_in_group_of_files($controller_files, $merged_files, 'controller');

        $data["controllers"] = $controller_files;
        $data["models"] = $model_files;

        helper('number_helper');
        //var_dump($controller_files[0]['functions_with_usage']);
        return view('code_review', $data);
    }

    /**
     * Compile a list of files in a folder
     * @param string $folder
     */
    private function list_files_in_folder($folder)
    {
        helper('filesystem');
        return directory_map(APPPATH . $folder);
    }

    /**
     * Open each files, compile a list of functions within
     * @param string $folder
     * @param array $array_of_filenames
     * @param $new_array
     */
    private function list_functions_in_files($folder, $array_of_filenames, &$new_array)
    {
        helper('file');
        foreach ($array_of_filenames as $key => $filename) {
            if (is_array($filename)) {
                $this->list_functions_in_files($folder . $key . '/', $filename, $new_array);
            } else {
                if (!in_array($filename, $this->file_exclude)) {
                    $new_array[] = array(
                        'filename' => $filename,
                        'info' => get_file_info(APPPATH . $folder . $filename),
                        'functions' => $this->discover_functions_in_content(APPPATH . $folder . $filename)
                    );
                }
            }
        }
        return $new_array;
    }

    // --------------------------------------------------------------------
    /**
     * Look through file content to find functions
     * @param string $file_content
     */
    private function discover_functions_in_content($file_path)
    {
        $file_content = file_get_contents($file_path);
        $functions_found = array();

        preg_match_all('/function\s+[A-z0-9_]*\(/', $file_content, $matches);

        if (!empty($matches[0])) {

            foreach ($matches[0] as $key => $match) {
                $func_name = str_replace('function ', '', $match);
                $func_name = trim(str_replace('(', '', $func_name));

                if (!in_array($func_name, $this->func_exclude)) {
                    $functions_found[] = $func_name;
                }
            }
        }
        return $functions_found;
    }

    // --------------------------------------------------------------------
    /**
     * Find $files_source array of files with methods being used in $files_target array of files
     * @param array $files_target
     * @param array $files_source
     * @return array files_source array
     */
    function function_usage_in_group_of_files($files_source, $files_target, $whose_funcions)
    {
        helper('file');
        $updated_files_source = array();
        foreach ($files_source as $file_source) {
            $file_source_name = str_replace(".php", "", $file_source['filename']);
            $file_source_functions = $file_source['functions'];
            foreach ($file_source_functions as $function_source_name) {
                $usage = array();
                foreach ($files_target as $file_target) {
                    $file_target_content = file_get_contents($file_target["info"]["server_path"]);
                    if ($whose_funcions == 'model') {
                        $pattern = '/(' . $file_source_name . '|this)->' . $function_source_name . '/i';
                        preg_match_all($pattern, $file_target_content, $matches);
                    } elseif ($whose_funcions == 'controller') {
                        $pattern = '/(' . $file_source_name . '(\/|->)' . $function_source_name . ')|(this->' . $function_source_name . ')/i';
                        preg_match_all($pattern, $file_target_content, $matches);
                    }
                    if (!empty($matches[0])) {
                        $usage[] = $function_source_name . ' used in the file ' . $file_target["info"]["server_path"];
                    }
                }


                $res = $this->findFunctionInRoutes($file_source_name, $function_source_name);
                if ($res != null) {
                    $usage[] = $function_source_name . ' used in the route ' . $res[0];
                    //var_dump($usage);
                }

                if (!isset($file_source["functions_with_usage"][$function_source_name]["usage"])) {
                    $file_source["functions_with_usage"][$function_source_name]["usage"] = array();
                }
                $file_source["functions_with_usage"][$function_source_name]["usage"] = array_merge($file_source["functions_with_usage"][$function_source_name]["usage"], $usage);
            }
            $updated_files_source[] = $file_source;
        }
        return $updated_files_source;
    }

    function findFunctionInRoutes($controller, $function)
    {
        $routesService = \Config\Services::routes();
        $routes = $routesService->getRoutes('get');
        $routes = array_merge($routes, $routesService->getRoutes('post'));
        foreach ($routes as $key => $route) {
            if (gettype($route) == 'string') {
                $res = strpos($route, "$controller::$function");
                if ($res) {
                    return [$key, $route];
                }
            }
        }
    }
}
