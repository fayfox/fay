<?php
namespace fay\core;

use fay\helpers\RuntimeHelper;
use fay\helpers\StringHelper;
use fay\helpers\UrlHelper;

class View{
    /**
     * 用于试图层的数据
     * @var array
     */
    private $_view_data = array();
    private $_null = null;
    
    private $_css = array();
    
    public function url($router = null, $params = array()){
        return UrlHelper::createUrl($router, $params);
    }
    
    /**
     * 返回public/apps/{APPLICATION}下的文件路径
     * 用于返回自定义application的静态文件
     * @param string $uri
     * @return string
     */
    public function appAssets($uri){
        return UrlHelper::appAssets($uri);
    }
    
    /**
     * 返回public/assets/下的文件路径（第三方jquery类库等）
     * 主要是考虑到以后如果要做静态资源分离，只要改这个函数就好了
     * @param string $uri
     * @return string
     */
    public function assets($uri){
        return UrlHelper::assets($uri);
    }
    
    /**
     * 向视图传递一堆参数
     * @param array $options
     * @return View
     */
    public function assign($options){
        $this->_view_data = array_merge($this->_view_data, $options);
        return $this;
    }
    
    /**
     * @return array
     */
    public function getViewData(){
        return $this->_view_data;
    }
    
    /**
     * 指定视图参数
     * @param string $key
     * @param mixed $value
     * @return void
     */
    public function __set($key, $value){
        $this->_view_data[$key] = $value;
    }
    
    public function &__get($key){
        if(isset($this->_view_data[$key])){
            return $this->_view_data[$key];
        }else{
            return $this->_null;//直接返回null的话，会报错
        }
    }
    
    public function appendCss($href){
        array_push($this->_css, $href);
    }
    
    public function prependCss($href){
        array_unshift($this->_css, $href);
    }
    
    public function getCss(){
        $html = '';
        foreach($this->_css as $css){
            $html .= '<link type="text/css" rel="stylesheet" href="'.$css.'" />'."\r\n";
        }
        return $html;
    }
    
    /**
     *渲染一个视图
     * @param string $view 视图文件
     * @param string $layout 模板文件目录
     * @return null|string
     */
    public function render($view = null, $layout = null){
        RuntimeHelper::append(__FILE__, __LINE__, '准备渲染视图');
        //触发事件
        \F::event()->trigger('before_render');
        
        $content = $this->renderPartial($view, $this->getViewData());
        RuntimeHelper::append(__FILE__, __LINE__, '视图渲染完成');

        if($layout === null){
            //默认为controller中公共layout
            $layout = \F::app()->layout_template;
        }
        
        if($layout && strpos($layout, '/') === false){
            //没有斜杠的情况下，默认为当前layouts下的文件
            $layout = "layouts/{$layout}";
        }
        
        if(!empty($layout)){
            $content = $this->renderPartial($layout, array_merge(
                $this->getViewData(),
                \F::app()->layout->getLayoutData(),
                array('content'=>$content)
            ));
        }
        
        return $content;
    }
    
    /**
     * 不带layout渲染一个视图
     * @param string $view
     * @param array $view_data 传参（此函数不调用全局的传参，只认传入的参数）
     * @param int $cache 局部缓存，大于0表示过期时间；等于0表示永不过期；小于0表示不缓存
     * @return null|string
     * @throws \ErrorException
     */
    public function renderPartial($view = null, $view_data = array(), $cache = -1){
        RuntimeHelper::append(__FILE__, __LINE__, '开始渲染视图: ' . $view);
        $uri = Uri::getInstance();
        $module = isset($uri->module) ? $uri->module : \F::config()->get('default_router.module');
        $package = null;
        //加载视图文件
        if($view === null){
            $action = StringHelper::case2underscore($uri->action);
            $controller = StringHelper::case2underscore($uri->controller);
            $view_relative_path = "modules/{$module}/views/{$controller}/{$action}.php";
        }else{
            $view_arr = explode('/', $view, 4);
            
            switch(count($view_arr)){
                case 1:
                    $controller = $uri->controller;
                    $action = $view_arr[0];
                break;
                case 2:
                    $controller = $view_arr[0];
                    $action = $view_arr[1];
                break;
                case 3:
                    $module = $view_arr[0];
                    $controller = $view_arr[1];
                    $action = $view_arr[2];
                break;
                case 4:
                default:
                    $package = $view_arr[0];
                    $module = $view_arr[1];
                    $controller = $view_arr[2];
                    $action = $view_arr[3];
                break;
            }
            
            //大小写分割转下划线分割
            $controller = StringHelper::case2underscore($controller);
            $action = StringHelper::case2underscore($action);
            $view_relative_path = "modules/{$module}/views/{$controller}/{$action}.php";
        }
        
        if($cache >= 0){
            //从缓存获取
            $cache_key = "partial/{$module}/{$controller}/{$action}";
            $content = \F::cache()->get($cache_key);
            if($content){
                return $content;
            }
        }
        
        if($package && $package != APPLICATION){//明确指定包名且包名不是当前app，直接在vendor/faysoft目录查找
            if(file_exists(FAYSOFT_PATH."{$package}/{$view_relative_path}")){
                //faysoft/*下的类库
                $view_path = FAYSOFT_PATH."{$package}/{$view_relative_path}";
            }
        }else{//未明确指定包名或包名为当前app
            if(file_exists(APPLICATION_PATH.$view_relative_path)){//先查找app目录
                //前台app
                $view_path = APPLICATION_PATH.$view_relative_path;
            }else{
                $addressing_path = \F::config()->get('addressing_path');//根据addressing_path依次查找vendor/faysoft/目录
                $addressing_path || $addressing_path = array();
                if($uri->package != APPLICATION){
                    //若当前package是faysoft/*下的项目，先搜索当前package
                    array_unshift($addressing_path, $uri->package);
                }
                foreach($addressing_path as $address){
                    if(file_exists(FAYSOFT_PATH."{$address}/{$view_relative_path}")){
                        //faysoft/*下的类库
                        $view_path = FAYSOFT_PATH."{$address}/{$view_relative_path}";
                        break;
                    }
                }
            }
        }
        if(empty($view_path) && file_exists(CMS_PATH."modules/tools/views/{$controller}/{$action}.php")){
            //最后搜索cms/tools下有没有默认文件，例如报错，分页条等
            $view_path = CMS_PATH."modules/tools/views/{$controller}/{$action}.php";
        }
        
        if(empty($view_path)){
            throw new \ErrorException('视图文件不存在', 'Relative Path: '.$view_relative_path);
        }else{
            $content = $this->obOutput($view_path, $view_data);
        }
        
        if(isset($cache_key)){
            //设置缓存
            \F::cache()->set($cache_key, $content, $cache);
        }
        
        return $content;
    }
    
    /**
     * eval执行一段代码，放在这个函数里是为了让eval的view层代码可以使用$this
     * @param string $__code__
     * @param array $__data__
     */
    public function evalCode($__code__, $__data__){
        extract($__data__, EXTR_SKIP);
        eval('?>'.$__code__.'<?php ');
    }
    
    /**
     * 独立一个渲染函数，防止变量污染
     * @param string $__view_path__ 视图文件路径
     * @param array $__view_data__ 传递变量
     * @return string
     */
    private function obOutput($__view_path__, $__view_data__ = array()){
        extract($__view_data__, EXTR_SKIP);
        ob_start();
        include $__view_path__;
        $content = ob_get_contents();
        ob_end_clean();
        
        return $content;
    }
}