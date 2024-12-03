<?php
defined('BASEPATH') OR exit('No direct script access allowed');

$autoload['packages'] = array();

$autoload['libraries'] = array('database','session','encryption', 'pagination', 'form_validation');

$autoload['drivers'] = array();

$autoload['helper'] = array('url', 'file', 'form', 'download', 'cookie', 'api_helper', 'app_helper', 'response_helper', 'form_helper');

$autoload['config'] = array();

$autoload['language'] = array();

$autoload['model'] = array('Dbhelper');