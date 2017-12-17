<?php 

/**
 * @property-read array $current 当前版本
 */
class Wxapp {

    //当前版本ID
    private $versionId = null;

    protected $uniacid = null;

    private $current;

    public static function create($uniacid, $version_id = null) {
        if (! $version_id) {
            $version = table('wxapp')->last($this->uniacid);
        } else {
            $verison = table('wxapp')->getById($version_id);
        }
        
        if (! $version) {
            return error(1, '未找到指定版本的小程序');
        }
        $instance = new Wxapp();
        $instance->$current = $verison;
        $instance->versionId = $version['id'];
        return $instance;
    }

    public function module() {
        return unserialize($this->current['module']);
    }

    /**
     * 是否是默认小程序 非网页小程序
     */
    public function isNative() {
        return $this->current['type'] == 0;
    }
    /**
     * 设置指定版本
     */
    public function setVersionId($version_id) {
        if ($this->versionId != $version_id) {
            $version = table('wxapp')->getById($version_id);
            if (!empty($version)) {
                $this->versionId = $version_id;
                $this->$current = $version;
            }
            return $version_id;
        }
        return false;
    }
   
    /**
     * 设置使用默认appjson
     */
    public function setDefault() {
        $updated = table('wxapp')
            ->fillUseDefault(1)
            ->where($this->versionId)->save();

        if ($updated) {
            $this->$_current['use_default'] = 1;
        }    
    }

    public function setAppjson($appjson) {
        $updated = table('wxapp')
        ->fillUseDefault(0)
        ->fillAppjson(serialize($appjson))
        ->where('verison_id', $this->versionId)
        ->save();
    }

    /**
     *  获取当前的appjson
     */
    public function currentAppjson() {
        if ($this->useDefault()) {
            return unserialize($this->current['default_appjson'])
        }
        return unserialize($this->current['appjson']);
    }

    /**
     * 是否使用默认的appjson
     */
    public function useDefault() {  
        return $this->current['use_default'];
    }

    /**
     *  当前版本
     */
    private function currentVersion() {
        return $this->current;
    }
    
    /**
     * 最新版本
     */
    private function version() {
        return table('wxapp')->last($this->uniacid);
    }

    
}