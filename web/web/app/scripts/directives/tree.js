/**
 * Created by su on 14-3-18.
 */
'use strict';

angular.module('weChatHrApp.directive')
    .service('tree',['$rootScope',function($rootScope){
        this.show = function(){
            $rootScope.$broadcast('tree-new');
        };
    }])
    .directive('treeContainer',['tree','cache','httpProtocol','$timeout',function(tree,cache,httpProtocol,$timeout){
        return {
            replace: true,
            restrict: 'EA',
            scope: {},
            link:function (scope, elm, attrs){
                var  options={
                    mul:false,//是否多选
                    type:'department',// department OR user OR tag
                    confirmText:'确定',
                    title:'选择部门',
                    cancelText:'取消'
                };
                scope.selected = {
                    department:[],
                    user:[],
                    tag:[]
                };

                scope.$on('tree-new', function () {
                    var obj = cache.get('data');
                    //初始化数据
                    if(obj.options){
                        angular.extend(options,obj.options);
                    }
                    scope.data=obj.data;
                    scope.confirmText=options.confirmText;
                    scope.cancelText=options.cancelText;
                    scope.title=options.title;
                    scope.type=options.type;
                    if(obj.selected){
                        scope.selected[options.type]=obj.selected;
                    }
                    var ids=[];
                    angular.forEach(scope.selected[options.type], function (item) {
                        ids.push(item.id);
                    });
                    var result =tree.idInArr(ids, scope.data, 'children');
                    angular.forEach(result, function (rItem) {
                        rItem.isSelected = true;
                    });

                    scope.openTree = true;//显示选择
                });
                scope.folder=function(item){
                    item.isShow = !item.isShow;
                };
                //更改部门选项
                scope.changeSelected=function(item,e){
                    e.stopPropagation();
                    //e.preventDefault();

                    item.isSelected = !item.isSelected;

                    if(item.isSelected){
                        if (!options.mul && scope.selected[options.type].length) {
                            var ids=[];
                            angular.forEach(scope.selected[options.type], function (sItem,index) {
                                if(sItem.id!=item.id){
                                    scope.selected[options.type].splice(index, 1);
                                    ids.push(sItem.id)
                                }
                            });
                            var result=[];
                            if(options.type=='department'){
                                 result =tree.idInArr(ids, scope.data, 'children');
                            }else{
                                 result =tree.idInArr(ids, scope.userList);
                            }

                            angular.forEach(result, function (rItem) {
                                rItem.isSelected = false;
                            });

                        }

                        scope.selected[options.type].push({
                            'id': item.id,
                            'name': item.name,
                            'picture': item.picture,
                            'fake_id': item.fake_id
                        });
                    }else{
                        for (var i = 0; i < scope.selected[options.type].length; i++) {
                            if (item.id == scope.selected[options.type][i].id) {
                                scope.selected[options.type].splice(i, 1);
                                break;
                            }
                        }
                    }
                };

                //获取用户列表相关
                scope.openUser=false;
                scope.current_department_id='';
                scope.userList=[];
                scope.userHasMore=false;
                scope.userLoading=false;
                scope.placeHolderTitle='';
                scope.alphabetList=['A','B','C','D','E','F','G','H','I','J','K','L','M','N','O','P','Q','R','S','T','U','V','W','X','Y','Z'];
                var initWithUserData=function(data){
                    if(!data || !data.list){
                        return false;
                    }
                    if(scope.userList.length>0){
                        scope.userList = scope.userList.concat(data.list);
                    }else{
                        scope.userList = data.list;
                    }
                    scope.userHasMore = data.hasMore;

                };
                scope.userMore = function(){
                    if(scope.userLoading){
                        return;
                    }
                    var len=scope.userList.length;
                    var cursor_id=scope.userList[len-1].cursor_id;
                    scope.loading = true;
                    httpProtocol.wpost({id:scope.current_department_id,cursor_id:cursor_id,length:20},httpProtocol.POST_TYPE.GET_USER_LIST_WITH_DEPARTMENT).then(function(data){
                        scope.userLoading = false;
                        initWithUserData(data);
                    },function(){
                        scope.userLoading = false;
                    });
                };

                scope.changeUserSelected=function(item,e){
                    e.stopPropagation();
                    scope.userList=[];
                    scope.placeHolderTitle=item;
                    scope.current_department_id=item.id;
                    var cacheData=cache.get('user_list_data_'+scope.current_department_id);

                    if(cacheData){
                        initWithUserData(cacheData);
                        scope.openUser=true;
                        return false;
                    }

                    httpProtocol.wpost({id:item.id,cursor_id:0,length:20},httpProtocol.POST_TYPE.GET_USER_LIST_WITH_DEPARTMENT).then(function(data){
                        cache.put('user_list_data_'+scope.current_department_id,data);
                        initWithUserData(data);
                        scope.openUser=true;
                    });
                };


                //确认取消
                scope.cancelUser=function(e){//用户列表返回
                    e.stopPropagation();
                    window.console.log(scope.isSearch);
                    if(scope.isSearch){
                        scope.mlcSearchKeys='';
                        scope.searchList=[];
                        scope.isSearch=false;
                    }else{
                        scope.selected[options.type]=[];
                        scope.openUser=false;
                    }

                };
                //部门列表确认取消
                scope.confirm= function () {
                    scope.select=scope.selected[options.type];
                    scope.openTree = !scope.openTree;

                    $timeout(function(){
                        scope.openUser=false;
                        scope.mlcSearchKeys='';
                        scope.searchList=[];
                        scope.isSearch=false;
                    },300);
                };
                scope.cancel= function () {
                    scope.openTree = !scope.openTree;
                    scope.mlcSearchKeys='';
                    scope.searchList=[];
                    scope.isSearch=false;
                };

                //搜索相关
                scope.isSearch=false;
                scope.mlcSearchKeys='';
                scope.searchList=[];
                scope.searchCache = {
                    text: '',
                    time: {},
                    timer: '',
                    list: {}
                };

                scope.searchFocus = function () {
                    scope.isSearch=true;
                };
                scope.cancelSearch = function () {
                    scope.mlcSearchKeys='';
                    scope.searchList=[];
                    scope.isSearch=false;
                };

                var searchUser=function(){
                    scope.searchList=[];
                    window.console.log(scope.mlcSearchKeys);
                    var key_words=scope.mlcSearchKeys.toString().replace(/[^\w\u4e00-\u9fa5]*/g, '').trim();
                    window.console.log(key_words);
                    if(!key_words){
                        return false;
                    }
                    var cacheData=scope.searchCache.list['cache_'+scope.current_department_id+'_'+key_words];

                    window.console.log(cacheData);
                    if(cacheData){
                        scope.searchList=cacheData;
                        return false;
                    }

                    httpProtocol.wpost({id:scope.current_department_id,key_words:key_words,cursor_id:0,length:8},httpProtocol.POST_TYPE.GET_USER_LIST_WITH_DEPARTMENT).then(function(data){
                        scope.searchList=data;
                        scope.searchCache.list['cache_'+scope.current_department_id+'_'+key_words] = data;
                    });
                };
                scope.searchUser=function(){
                    scope.searchCache.time.time = Date.now();
                    scope.searchCache.timer = $timeout(function () {
                        searchUser();
                    }, 300);
                };
                scope.$watch('searchCache.time', function (newValue, oldValue) {
                    if (newValue !== oldValue) {
                        if (newValue && oldValue) {
                            if (newValue.time - oldValue.time < 300) {
                                $timeout.cancel(scope.searchCache.timer);
                            }
                        }
                    }
                }, true);

                angular.element('#mlc_user_list_area').bind('scroll',function(){
                    var top=this.scrollTop;
                    var height=this.offsetHeight;
                    var ss=angular.element('#li_9').offset().top;
                    //if(top>200){
                    //    this.scrollTop=ss;
                    //}
                    //window.console.log(height);
                    window.console.log(ss);
                    window.console.log(top);
                    window.console.log(top+ss);
                });

            },
            //controller:['$scope',function($scope){
            //    $scope.openTree = false;
            //}],
            templateUrl:'/views/tree.html'
        };
    }]);