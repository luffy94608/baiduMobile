$(document).ready(function(){
    $('#loading_page').hide();//隐藏loading page
    var busPoint='';
    var busMarker='';

    var map = new BMap.Map("mapContainer");
    var point = new BMap.Point(116.331398,39.897445);
    map.centerAndZoom(point, 12);
    var top_right_navigation=new BMap.NavigationControl({anchor: BMAP_ANCHOR_TOP_RIGHT, type: BMAP_NAVIGATION_CONTROL_ZOOM}); //右上角，仅包含平移和缩放按钮
    map.addControl(top_right_navigation);


    /**
     * 定位班车
     * @param busPoint
     */
    var initBusPointMap=function(lng,lat,isSetView)
    {
        // 百度地图API功能
        var initPoint = [];
        var pItem = new BMap.Point(lng, lat);
        initPoint.push(pItem);

        busPoint=initPoint[0];
        //map.panTo(initPoint);
        // 百度地图API功能
        if(isSetView){
            map.setViewport(initPoint);
        }
        //http://developer.baidu.com/map/jsdemo/img/car.png
        var myIcon = new BMap.Icon("http://wxdev.hollo.cn/images/icon-bus-position.png", new BMap.Size(80, 80), {
            imageSize: new BMap.Size(40, 40),
        });
        map.removeOverlay(busMarker);
        busMarker = new BMap.Marker(initPoint[0],{icon:myIcon,offset:new BMap.Size(13, 8)});  // 创建标注

        map.addOverlay(busMarker);               // 将标注添加到地图中
        busMarker.setAnimation(BMAP_ANIMATION_BOUNCE); //跳动的动画

    };
    initBusPointMap(116.307907,40.056941,true);


    var meMarker='';
    $('.js_position_me').unbind().bind('click',function(){
        if (navigator.geolocation)
        {
            var options = {timeout:60000};
            navigator.geolocation.getCurrentPosition(function(position){
                var lng = position.coords.longitude;
                var lat = position.coords.latitude;

                $.ajax({
                    type:'POST',
                    url:'http://wxdev.hollo.cn/api/map/translate',
                    data: {lng:lng, lat:lat,type:'jsonp'},
                    dataType:'jsonp',
                    jsonp: "callback",
                    success:function(data){
                        map.removeOverlay(meMarker);
                        var mePoint= new BMap.Point(data[0].x,data[0].y);
                        meMarker = new BMap.Marker(mePoint);
                        map.addOverlay(meMarker);
                        map.setViewport([mePoint]);
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

    });

});