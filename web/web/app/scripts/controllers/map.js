'use strict';

angular.module('weChatHrApp')
    .controller('MapCtrl', function (APP_URL,$rootScope,$scope,initData,$routeParams,$location,$timeout,$interval) {
        $scope.info=initData.dept;

        // 百度地图初始化
        var map = new BMap.Map("mapContainer");
        var centerPoint = new BMap.Point(116.331398,39.897445);
        map.centerAndZoom(centerPoint, 11);
        var top_right_navigation=new BMap.NavigationControl({anchor: BMAP_ANCHOR_TOP_RIGHT, type: BMAP_NAVIGATION_CONTROL_ZOOM}); //右上角，仅包含平移和缩放按钮
        map.addControl(top_right_navigation);
        /**
         * 定位班车
         * @param lng
         * @param lat
         * @param info
         * @param isSetView
         */
        var initBusPointMap=function(lng,lat,info,isSetView)
        {
            // 百度地图API功能
            var busMarker;
            var initPoint = [];
            var pItem = new BMap.Point(lng, lat);
            initPoint.push(pItem);
            // 百度地图API功能
            if(isSetView){
                map.setViewport(initPoint);
            }
            var myIcon = new BMap.Icon(APP_URL+"/images/icon-bus-position.png", new BMap.Size(40, 40), {
                imageSize: new BMap.Size(40, 40),
            });
            map.removeOverlay(busMarker);
            busMarker = new BMap.Marker(initPoint[0],{icon:myIcon,offset:new BMap.Size(0, -20)});  // 创建标注
            var content ='集合地点'+(i+1) +'<br/>'+info.verbose+'<br/>'+info.arrived_at;
            var opts2 = {
                position : initPoint[0],    // 指定文本标注所在的地理位置
                offset   : new BMap.Size(0, 0)    //设置文本偏移量
            };
            var label = new BMap.Label(content, opts2);  // 创建文本标注对象
            label.setStyle({
                color:"#fff",
                backgroundColor:'#FC8825',
                fontSize:"14px",
                lineHeight:"20px",
                fontWeight:"normal",
                padding:'10px',
                borderRadius:'4px',
                fontFamily:"微软雅黑",
                border:'none'
            });
            //map.addOverlay(label);
            map.addOverlay(busMarker);               // 将标注添加到地图中
            addClickHandler(content,busMarker);
            //busMarker.setAnimation(BMAP_ANIMATION_BOUNCE); //跳动的动画

        };
        var opts = {
            //width : 250,     // 信息窗口宽度
            //height: 80,     // 信息窗口高度
            title : "信息窗口" , // 信息窗口标题
            enableMessage:false//设置允许信息窗发送短息
        };
        function addClickHandler(content,marker){
            marker.addEventListener("click",function(e){
                    openInfo(content,e)}
            );
        }
        function openInfo(content,e){
            var p = e.target;
            var point = new BMap.Point(p.getPosition().lng, p.getPosition().lat);
            var infoWindow = new BMap.InfoWindow(content,opts);  // 创建信息窗口对象
            map.openInfoWindow(infoWindow,point); //开启信息窗口
        }

        var latlngs =$scope.info;
        for(var i=0;i<latlngs.length;i++){
            initBusPointMap(latlngs[i].location.lng,latlngs[i].location.lat,latlngs[i],true);
        }
        /**
         * 实时位置获取
         */
        var isRequesting=false;
        var initCount=0;
        var $timer;
        var timeoutLocationBus=function(){
            var initAutoGetLocationData=function(timer){
                var id=$scope.info.bus_path_schedule_id;
                if(isRequesting || !id){
                    return false;
                }
                isRequesting=true;
                initCount++;
                httpProtocol.wpost({id:id},httpProtocol.POST_TYPE.SEARCH_BUS_PLACE,'',true).then(function(data){
                    isRequesting=false;
                    if(data){
                        var hasSetView;
                        if(initCount==1){
                            hasSetView=true;
                        }else{
                            hasSetView=false;
                        }
                        if(data.cur_loc){
                            $scope.currentLocation=data;
                            initBusPointMap(data.cur_loc.lng,data.cur_loc.lat,'',hasSetView);
                            return false;
                        }
                    }else{
                        map.removeOverlay(busMarker);
                        $scope.currentLocation='';
                    }
                    if(timer){
                        //$interval.cancel(timer);
                    }

                },function(){
                    isRequesting=false;
                });
            };
            initAutoGetLocationData();
            $timer=$interval(function(){
                initAutoGetLocationData($timer);
            },5000);
        };


        /**
         * 调到百度web地图进行导航
         * @param item
         */
        $scope.toLocation=function(item){
            var url='http://api.map.baidu.com/marker?location='+item.location.lat+','+item.location.lng+'&title='+item.short_name+'&content='+item.verbose+'&output=html';
            window.location.href=url;
        };
        $scope.panTo=function(item){
            var movePoint=new BMap.Point( item.location.lng,item.location.lat);
            map.panTo(movePoint);
        };
        /**
         * 轮播图
         * @type {Array}
         */
        $scope.slides=[];
        if($scope.info && $scope.info.length)
        {
            for(var i=0;i<$scope.info.length;i++){
                var item={
                    id: i,
                    label: 'slide #' +(i),
                    short_name: $scope.info[i].short_name,
                    verbose: $scope.info[i].verbose,
                    arrived_at: $scope.info[i].arrived_at,
                    location: $scope.info[i].location,
                    odd: (i % 2 === 0)
                };
                $scope.slides.push(item);
            }
        }

        /**
         * 去除百度地图logo
         */
        $timeout(function(){
            $('.anchorBL').remove();
        },1000);
        $('.map-body').css('height',$(window).height());

    });
