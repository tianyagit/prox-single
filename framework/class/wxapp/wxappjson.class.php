<?php
/**
 * [WeEngine System] Copyright (c) 2013 WE7.CC
 */
class WxappJson {

    const WXAPP_VERSIONS = 'wxapp_versions';
    /**
     *  wxapp_versions 字段数组
     */
    private $attrs;
    private $version_id;

    public function __construct() {
        
    }

    public static function find($version_id) {
        $attrs = pdo_get(static::WXAPP_VERSIONS, array('id'=>$version_id));
        $instance = new WxappJson();
        $instance->attrs = $attrs;
        $instance->version_id = $version_id;
        return $instance;
    }

    public function appjson() {
        if ($this->useDefault()) {
            return $this->defaultAppjson();
        }
        return $this->customAppjson();
    }

    /**
     * 自定义appjson
     */
    private function customAppjson($to_cloud = false) {
        $appjson = $this->appjson ? unserialize($this->appjson) : null;
        if (!$to_cloud) {
            return $appjson;
        }
    }

    /**
     * 是否使用默认appjson
     * @return boolean 
     */
    public function useDefault() {
        return $this->use_default;
    }

    /**
     * 设为使用默认appjson
     * 
     */
    public function setDefault() {
        $result = pdo_update(self::WXAPP_VERSIONS, 
            array('use_default'=>1, 'appjson'=>null), array('id'=>$version_id));
        if ($result) {
            $this->attrs['use_default'] = 1;
        }    
        return $result;
    }

  

    /**
     * @return array appjson
     */
    private function defaultAppjson() {
        if(!$this->default_appjson) {
            $appjson = $this->cloudAppJson();
            if($appjson) {
                pdo_update(self::WXAPP_VERSIONS, 
                    array('default_appjson'=>serialize($appjson)), 
                    array('id'=>$version_id));
                return $appjson;    
            }
        }
        return $this->default_appjson;
    }

      /**
     * 云服务appjson
     */
    private function cloudAppJson() {
        global $_W;
        load()->classs('cloudapi');
        $cloud_api = new CloudApi();
        $commit_data = array('do' => 'appjson',
            'modules' => $this->attributes['modules'],
        );
        $data = $cloud_api->get('wxapp', 'upload2', $commit_data,
            'json', false);

        if(is_error($data)) {
            return null;
        }
        return $data;
    }
    

    public function __get($key) {
        return isset($this->attrs[$key]) ? $this->attrs[$key] : null;
    }
}