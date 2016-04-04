<?php

/**
 * Created by PhpStorm.
 * User: karboom
 * Date: 16-3-26
 * Time: 下午12:15
 */
namespace Karboom;

class RestfulParser
{
    public $default_per_page;
    public $id_regs;

    public function __construct($per_page = 20, $id_regs = array()) {
        $this->default_per_page = $per_page;
        $this->id_regs = $id_regs;
    }

    private function match_id ($string) {
        foreach($this->id_regs as $reg) {
            if (1 === preg_match($reg, $string)) return true;
        }

        return false;
    }

    private function parse_filter($key, $val) {
        $prefix_op = array(
            'max_' => '<',
            'min_' => '>',
            'exc_' => '!=',
            'inc_' => 'in'
        );

        // set default condition
        list($op, $name) = array('=', $key);

        // check key
        foreach ($prefix_op as $prefix=>$op_token) {
            if ( 0 === strpos($key, $prefix)) {
                list($op, $name) = array($op_token, substr($key, strlen($prefix)));
            }
        }

        // change val by key
        if ('in' == $op) {
            $val = preg_split('/,/', $val);
        }

        return array(
            'name' => $name,
            'op' => $op,
            'value' => $val
        );
    }

    public function parse ($url, $headers, $body) {
        $result = array(
            'paths' =>  array(),
            'filters' => array(),
            'sort' => array(),
            'skip' =>  0,
            'limit' =>  $this->default_per_page,
            'fields' => array(),
            'headers' =>  $headers,
            'body' =>  $body
        );

        list($url, $query_string) = preg_split('/\?/', $url);

        foreach(preg_split('/\//', $url) as $segment) {
            if ($this->match_id($segment)){
                $paths = &$result['paths'];
                $paths[count($paths) - 1]['id'] = $segment;
            } else if (!empty($segment)) {
                array_push($result['paths'], array('name'=> $segment));
            }
        }

        $reserve = array('per_page', 'page', 'sort', 'fields');

        parse_str($query_string, $query);
        foreach ($query as $key => $val) {
            if (in_array($key, $reserve)) continue;

            array_push($result['filters'], $this->parse_filter($key, $val));
        }

        if (!empty($query['per_page'])) {
            $result['limit'] = $query['per_page'];
        }

        if (!empty($query['page'])) {
            $result['skip'] = ($query['page'] - 1) * $result['limit'];
        }

        if (!empty($query['fields'])) {
            $result['fields'] = preg_split('/,/', $query['fields']);
        }

        return $result;
    }
}

