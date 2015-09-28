$(document).ready(function(){
    $('#loading_page').hide();//隐藏loading page
    var busPoint=[];
    var currentIndex=0;
    var busMarker='';
    var host='http://baidu.hollo.cn';

    /**
     * 处理窗口高度移除百度标示
     */
    $('#mapContainer').css('height',$(window).height());
    setTimeout(function(){
        $('.anchorBL').remove();
    },600);
    /**
     * 初始化地图
     * @type {BMap.Map}
     */
    var map = new BMap.Map("mapContainer");
    var point = new BMap.Point(116.331398,39.897445);
    map.centerAndZoom(point, 12);
    var top_right_navigation=new BMap.NavigationControl({anchor: BMAP_ANCHOR_TOP_RIGHT, type: BMAP_NAVIGATION_CONTROL_ZOOM}); //右上角，仅包含平移和缩放按钮
    map.addControl(top_right_navigation);
    //map.addControl(new BMap.ZoomControl()); //添加地图缩放控件

    /**
     * 初始化坐标
     * @param lng
     * @param lat
     * @param isSetView
     */
    var initBusPointMap=function(lng,lat,isSetView)
    {
        // 百度地图API功能
        var initPoint = [];
        var pItem = new BMap.Point(lng, lat);
        initPoint.push(pItem);
        busPoint.push(pItem);

        //map.panTo(initPoint);
        // 百度地图API功能
        if(isSetView){
            map.setViewport(busPoint);
        }
        //http://developer.baidu.com/map/jsdemo/img/car.png
        var myIcon = new BMap.Icon(host+"/images/icon-bus-position.png", new BMap.Size(40, 40), {
            imageSize: new BMap.Size(40, 40),
        });

        //map.removeOverlay(busMarker);
        busMarker = new BMap.Marker(initPoint[0],{icon:myIcon,offset:new BMap.Size(0, -20)});  // 创建标注

        map.addOverlay(busMarker);               // 将标注添加到地图中
        busMarker.setAnimation(BMAP_ANIMATION_BOUNCE); //跳动的动画

    };
    //initBusPointMap(116.307907,40.056941,true);//中关村
    //initBusPointMap(116.301324,40.055343,true);//百度
    //initBusPointMap(116.330152,39.965082,true);//寰太大厦

    /**
     * 定位班车
     */
    $('.js_position_bus').unbind().bind('click',function() {
        alert('无班车信息');
        return false;
        map.setViewport([busPoint[currentIndex]]);
        var max=busPoint.length;
        if(busPoint[currentIndex]){
            map.panTo(busPoint[currentIndex]);
            currentIndex+=1;
            if(currentIndex>(max-1)){
                currentIndex=0;
            }
        }
    });
    /**
     * 定位我
     * @type {string}
     */
    var meMarker='';
    $('.js_position_me').unbind().bind('click',function(){
        alert(11)
        if(typeof BdHiJs !='undefined'){
            BdHiJs.device.geolocation.get({
                onSuccess:function(){
                    alert('定位成功');
                },
                onfail:function(){
                    alert('定位失败');
                },
                listener:function(data){
                    alert(data);
                    map.removeOverlay(meMarker);
                    var mePoint= new BMap.Point(data.longitude,data.latitude);
                    var meIcon = new BMap.Icon(host+"/images/icon-position-me.png", new BMap.Size(67, 67), {
                        imageSize: new BMap.Size(67, 67),
                    });
                    meMarker = new BMap.Marker(mePoint,{icon:meIcon,offset:new BMap.Size(0, -33)});
                    map.addOverlay(meMarker);
                    map.panTo(mePoint);
                }

            });
        }else{
            if (navigator.geolocation)
            {
                var options = {timeout:30000};
                navigator.geolocation.getCurrentPosition(function(position){
                    var lng = position.coords.longitude;
                    var lat = position.coords.latitude;

                    $.ajax({
                        type:'POST',
                        url:'http://wxdev.hollo.cn/api/map/translate',
                        data: {lng:lng, lat:lat,type:'jsonp'},
                        dataType:'jsonp',
                        async:true,
                        jsonp: "callback",
                        success:function(data){
                            map.removeOverlay(meMarker);
                            var mePoint= new BMap.Point(data[0].x,data[0].y);
                            var meIcon = new BMap.Icon(host+"/images/icon-position-me.png", new BMap.Size(67, 67), {
                                imageSize: new BMap.Size(67, 67),
                            });
                            meMarker = new BMap.Marker(mePoint,{icon:meIcon,offset:new BMap.Size(0, -33)});
                            map.addOverlay(meMarker);
                            //map.setViewport([mePoint]);
                            map.panTo(mePoint);
                        }
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

    });

});