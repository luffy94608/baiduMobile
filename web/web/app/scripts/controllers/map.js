'use strict';

angular.module('weChatHrApp')
    .controller('MapCtrl', function (APP_URL,$rootScope,$scope,initData,$routeParams,$location,httpProtocol,$timeout,$interval) {
        $scope.info='';
        $scope.indexMap={};
        $scope.id=$routeParams['id'];
        $scope.index=0;
        $scope.currentindex=0;
        $scope.currentLocation='';
        $scope.busMarkers={};
        $('#loading_page').hide();//隐藏loading page

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
            map.removeOverlay($scope.busMarkers[info.line_schedule_id]);

            busMarker = new BMap.Marker(initPoint[0],{icon:myIcon,offset:new BMap.Size(0, -20)});  // 创建标注
            var content ='当前位置：'+info.cur_pos+'<br/>下一站：'+info.next_station_name+'<br/>预计时间：'+info.next_station_arrive_time;
            map.addOverlay(busMarker);               // 将标注添加到地图中
            addClickHandler(content,busMarker);
            $scope.busMarkers[info.line_schedule_id]=busMarker;
            //busMarker.setAnimation(BMAP_ANIMATION_BOUNCE); //跳动的动画
        };
        function addClickHandler(content,marker){
            marker.addEventListener("click",function(e){
                    openInfo(content,e)}
            );
        }
        function openInfo(content,e){
            var opts = {
                width : 250,     // 信息窗口宽度
                //height: 80,     // 信息窗口高度
                title : "班车位置" , // 信息窗口标题
                enableMessage:false//设置允许信息窗发送短息
            };
            var p = e.target;
            var point = new BMap.Point(p.getPosition().lng, p.getPosition().lat);
            var infoWindow = new BMap.InfoWindow(content,opts);  // 创建信息窗口对象
            map.openInfoWindow(infoWindow,point); //开启信息窗口
        }


        /**
         * 初始化数据
         */
        var initWithData=function(data){
            if(!data || !data.nearby_buses){
                return false;
            }
            /**
             * 初始化坐标点
             */
            $scope.info=data.nearby_buses;
            for(var i=0;i<$scope.info.length;i++){
                initBusPointMap($scope.info[i].cur_loc.lng,$scope.info[i].cur_loc.lat,$scope.info[i],true);
            }
            /**
             * 轮播图初始化
             * @type {Array}
             */
            $scope.slides=[];
            if($scope.info && $scope.info.length)
            {
                for(var i=0;i<$scope.info.length;i++){
                    $scope.indexMap[$scope.info[i].line_schedule_id]=i;
                    var item={
                        id: i,
                        label: 'slide #' +(i),
                        info: $scope.info[i],
                        name: $scope.info[i].name,
                        odd: (i % 2 === 0)
                    };
                    $scope.slides.push(item);
                }
                $scope.index=$scope.indexMap[$scope.id];
            }


        };
        initWithData(initData);

        /**
         * 实时位置获取
         */
        var isRequesting=false;
        var initCount=0;
        var $timer;
        var timeoutLocationBus=function(){
            initCount=0;
            if($timer){
                $interval.cancel($timer);
            }
            var initAutoGetLocationData=function(timer){
                var id=$scope.currentLocation.line_schedule_id;
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
                        data.line_schedule_id=id;
                        $scope.slides[$scope.indexMap[id]].info=data;
                        if(data.cur_loc){
                            initBusPointMap(data.cur_loc.lng,data.cur_loc.lat,data,hasSetView);
                            return false;
                        }
                    }else{
                        map.removeOverlay($scope.busMarkers[id]);
                        $scope.slides[$scope.indexMap[id]].info.is_close=true;
                        //if($timer){
                        //    $interval.cancel($timer);
                        //}
                    }


                },function(){
                    isRequesting=false;
                });
            };
            $timer=$interval(function(){
                initAutoGetLocationData($timer);
            },5000);
        };

        /**
         * 坐标切换
         */
        $scope.panTo=function(item,index){
            if(item.is_close){
                return false;
            }
            if(item.cur_loc && item.cur_loc.lng && item.cur_loc.lat){
                var movePoint=new BMap.Point( item.cur_loc.lng,item.cur_loc.lat);
                map.panTo(movePoint);
                $scope.currentLocation=item;
                $scope.currentindex=index;
                //timeoutLocationBus();
            }
        };

        /**
         * 调到百度web地图进行导航
         * @param item
         */
        $scope.toLocation=function(item){
            return false;
            var url='http://api.map.baidu.com/marker?location='+item.cur_loc.lat+','+item.location.cur_loc+'&title='+item.name+'&content='+item.name+'&output=html';
            window.location.href=url;
        };
        /**
         * 监控变量的变化
         */
        $scope.$watch('currentindex',function(nVal,oVal){
            timeoutLocationBus();
        });

        /**
         * 去除百度地图logo
         */
        $timeout(function(){
            $('.anchorBL').remove();
        },1000);
        $('.map-body').css('height',$(window).height());

        /**
         * 定位我
         * @type {string}
         */
        var meMarker='';
        $scope.locationMe=function($event){
            $event.stopPropagation();
            if(typeof BdHiJs !='undefined'){
                BdHiJs.device.geolocation.get({
                    onSuccess:function(){
                        alert('定位成功');
                    },
                    onfail:function(){
                        alert('定位失败');
                    },
                    listener:function(res){
                        map.removeOverlay(meMarker);
                        if(typeof res=='string'){
                            res=JSON.parse(res);
                        }
                        var mePoint= new BMap.Point(res.longitude,res.latitude);
                        var meIcon = new BMap.Icon(APP_URL+"/images/icon-position-me.png", new BMap.Size(67, 67), {
                            imageSize: new BMap.Size(67, 67),
                        });
                        meMarker = new BMap.Marker(mePoint,{icon:meIcon,offset:new BMap.Size(0, -33)});
                        map.addOverlay(meMarker);
                        map.setViewport([mePoint]);
                        setTimeout(function(){
                            map.panTo(mePoint);
                        },400);
                    }
                });
            }else{
                if (navigator.geolocation)
                {
                    var options = {timeout:30000};
                    navigator.geolocation.getCurrentPosition(function(position){
                        var lng = position.coords.longitude;
                        var lat = position.coords.latitude;
                        httpProtocol.wpost({lng:lng, lat:lat},httpProtocol.POST_TYPE.TRANSLATE).then(function(data){
                            map.removeOverlay(meMarker);
                            var mePoint= new BMap.Point(data[0].x,data[0].y);
                            var meIcon = new BMap.Icon(APP_URL+"/images/icon-position-me.png", new BMap.Size(67, 67), {
                                imageSize: new BMap.Size(67, 67),
                            });
                            meMarker = new BMap.Marker(mePoint,{icon:meIcon,offset:new BMap.Size(0, -33)});
                            map.addOverlay(meMarker);
                            //map.setViewport([mePoint]);
                            map.panTo(mePoint);
                        });

                    },function(data){
                        console.log(data);
                    },options);
                }
                else
                {
                    alert('浏览器不支持定位');
                }
            }

        };

    });
