<?php
/**
 * Created by PhpStorm.
 * User: Nicolas
 * Date: 13-12-17
 * Time: 19:25
 */

class ApiError extends REST_Controller
{
    public function __construct($config = 'rest')
    {
        parent::__construct($config);
        $this->response(NULL, REST_Controller::HTTP_METHOD_NOT_ALLOWED);
    }
}