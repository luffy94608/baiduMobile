"use strict";

angular.module("weChatHrApp.directive", [])
    .directive('uiLazyload' , ['$document' , '$window' ,function(document , window){

    var $ = function(ele){
        return angular.element(ele);
    }

    var elements = (function(){
        var _uid =0 ;
        var _list = [];

        return {

            // 获取图片集合
            push : function(ele){
                _list[_uid ++] = ele ;
            },

            // 从集合中删除已load的子集
            del : function(key){
                _list[key] && delete _list[key] ;
            },

            get : function(){
                return _list  ;
            },

            size : function(){
                return _list.length ;
            }

        }

    })();


    //  元素是否在可视区域
    var isVisible = function(ele){

        var  rect = ele[0].getBoundingClientRect();
        rect.offsetTop = ele[0].offsetTop

        if($(window)[0].parent.innerHeight < rect.offsetTop
            &&  $(window)[0].pageYOffset + $(window)[0].parent.innerHeight < rect.offsetTop
            ||  $(window)[0].pageYOffset >( rect.offsetTop + rect.height)) {
            return false;
        }else{
            return true;
        }
    }

    //  检查图片是否可见
    var checkImage = function(){
        var eles = elements.get();
        angular.forEach(eles ,function(v , k){
            if(v && v.elem){
                isVisible(v.elem) ? eles[k].load(k) : false ;
            }
        })
    };

    var initLazyload = function(){
        checkImage();
        $(window).on('scroll' , checkImage)
    };

    return {
        restrict : 'EA',
        scope : {
            watch : '='
        },
        link : function(scope , ele , attrs){

            ele.css({
                'background' : '#fff',
                'opacity' : 0,
                'transition' : 'opacity .3s',
                '-webkit-transition' : 'opacity .3s',
                'animation-duration': '.3s'
            });

            elements.push({
                elem : ele ,
                load : function(key){

                    ele.attr('src' ,attrs['uiLazyload']);

                    ele.on('load' , function(){
                        ele.css({
                            'opacity' : '1'
                        })
                    });
                    // 加载后从列队里删除
                    if(key >=0 ) elements.del(key);
                }
            });

            initLazyload();
        }
    }
}]);