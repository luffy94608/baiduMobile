'use strict';

angular.module('weChatHrApp')
    .controller('ListCtrl', function (IMG_HOST,APP_URL,$rootScope,$scope,httpProtocol,$location) {
        $rootScope.bg_white = true;
        $scope.imgHost=IMG_HOST;
        $scope.APP_URL=APP_URL;
        $scope.timestamp=0;
        $scope.cursor_id=0;
        $scope.list=[];
        $scope.lat='';
        $scope.lng='';
        $scope.loading=false;
        $scope.hasMore=false;

        $scope.toDetail=function(item)
        {
            if(!item.line_id){
                return false;
            }
            $location.url('/map?id='+item.line_schedule_id+'&lat='+$scope.lat+'&lng='+$scope.lng);
        };

        $scope.loadMore=function(){
            if($scope.loading ){
                return;
            }
            $scope.loading = true;
            httpProtocol.wpost({timestamp:$scope.timestamp,cursor_id:$scope.cursor_id,is_next:1},httpProtocol.POST_TYPE.GET_TRAVEL_LIST).then(function(data){
                $scope.loading = false;
                initWithData(data);
            },function(){
                $scope.loading = false;
            });
        };

        var initWithData=function(data){
            if(!data){
                return false;
            }
            if(data.nearby_buses && data.nearby_buses.length){
                $scope.hasMore=true;
            }else{
                $scope.hasMore=false;
            }
            if($scope.list.length>0){
                $scope.list=$scope.list.concat(data.nearby_buses);
            }else{
                $scope.list=data.nearby_buses;
            }

            $scope.timestamp=data.timestamp;
            if($scope.list && $scope.list.length){
                $scope.cursor_id=$scope.list[$scope.list.length-1].cursor_id;
            }
        };
        //initWithData(initData);

        var getInitData=function(){
            httpProtocol.wpost({lat:$scope.lat,lng:$scope.lng,timestamp:$scope.timestamp,cursor_id:$scope.cursor_id,is_next:1},httpProtocol.POST_TYPE.GET_TRAVEL_LIST).then(function(data){
                $('#loading_page').hide();//隐藏loading page
                initWithData(data);
            });
        };
        if(typeof BdHiJs !='undefined'){
            BdHiJs.device.geolocation.get({
                onSuccess:function(){
                    alert('定位成功');
                },
                onfail:function(){
                    alert('定位失败');
                },
                listener:function(res){
                    if(typeof res=='string'){
                        res=JSON.parse(res);
                    }
                    $scope.lat=res.latitude;
                    $scope.lng=res.longitude;
                    getInitData();
                }
            });
        }else{
            getInitData();
        }

    });
