'use strict';

angular.module('weChatHrApp')
    .controller('ListCtrl', function (IMG_HOST,APP_URL,$scope,initData,httpProtocol,$location) {
        $scope.imgHost=IMG_HOST;
        $scope.APP_URL=APP_URL;
        $scope.timestamp=0;
        $scope.cursor_id=0;
        $scope.list=[];
        $scope.loading=false;
        $scope.hasMore=false;

        $scope.toDetail=function(item)
        {
            if(!item.line_id){
                return false;
            }
            $location.url('/map/'+item.line_id);
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
            if(data.lines && data.lines.length){
                $scope.hasMore=true;
            }else{
                $scope.hasMore=false;
            }
            if($scope.list.length>0){
                $scope.list=$scope.list.concat(data.lines);
            }else{
                $scope.list=data.lines;
            }

            $scope.timestamp=data.timestamp;
            if($scope.list && $scope.list.length){
                $scope.cursor_id=$scope.list[$scope.list.length-1].cursor_id;
            }
        };
        initWithData(initData);

    });
