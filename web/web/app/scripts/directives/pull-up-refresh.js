/**
 * Created by su on 14-3-14.
 *
 * jslint indent: 4
 *
 */

'use strict';

angular.module('weChatHrApp')
    .directive('pullRefresh', function () {
        return {
            restrict: 'EA',
            link: function (scope, elements, attrs) {

                var pullUpEl, pullUpOffset;

                pullUpEl = elements[0];
                pullUpOffset = pullUpEl.offsetHeight;
                angular.extend(scope.$parent.myScrollOptions, {
                    'wrap': {
                        useTransition: true,
                        onRefresh: function () {
                            if (pullUpEl.className.match('loading')) {
                                pullUpEl.className = '';
                                pullUpEl.querySelector('.pullUpLabel').innerHTML = 'Pull up to load more...';
                            }
                        },
                        onScrollMove: function () {
                            if (!pullUpEl.className.match('loading')) {
                                if (this.y < (this.maxScrollY - 5) && !pullUpEl.className.match('flip')) {
                                    pullUpEl.className = 'flip';
                                    pullUpEl.querySelector('.pullUpLabel').innerHTML = 'Release to refresh...';
                                    this.maxScrollY = this.maxScrollY;
                                } else if (this.y > (this.maxScrollY + 5) && pullUpEl.className.match('flip')) {
                                    pullUpEl.className = '';
                                    pullUpEl.querySelector('.pullUpLabel').innerHTML = 'Pull up to load more...';
                                    this.maxScrollY = pullUpOffset;
                                }
                            }
                        },
                        onScrollEnd: function () {
                            if (!pullUpEl.className.match('loading')) {
                                if (pullUpEl.className.match('flip')) {
                                    pullUpEl.className = 'loading';
                                    pullUpEl.querySelector('.pullUpLabel').innerHTML = 'Loading...';
                                    scope.$apply(function () {
                                        scope.$eval(attrs.confirmAction);
                                    });

                                }
                            }
                        }
                    }
                });

            }
        };
    })
    .directive('whenScrolled', function () {
        return {
            scope: {
                hasMore: '=?whenEnd',
                whenScrolled: '&'
            },
            link: function (scope, elm, attr) {
                $(window).unbind('scroll').bind('scroll',function(){
                    //var height=elm.height();
                    var height=elm[0].offsetHeight;
                    var top=$(window).scrollTop();
                    var screenHeight=window.screen.height;
                    if(top>0 && height-top-screenHeight<=0){
                        if (scope.hasMore) {
                            scope.whenScrolled();
                        }
                    }
                });

            }
        };
    })
    .directive('whenScrolledCurrent', function () {
        return {
            scope: {
                hasMore: '=?whenEnd',
                whenScrolledCurrent: '&'
            },
            link: function (scope, elm, attr) {
                elm.bind('scroll',function(e){
                    var raw = elm[0];
                    if (raw.scrollHeight-raw.scrollTop - raw.offsetHeight < 10) {
                        if (scope.hasMore) {
                            scope.whenScrolledCurrent();
                        }
                    }
                })

            }
        };
    })
    //.directive('whenScrolled', function () {
    //    return {
    //        link: function (scope, elm, attr) {
    //            var raw = elm[0];
    //            console.log(raw.scrollTop + raw.offsetHeight);
    //            console.log(raw.scrollHeight);
    //            angular.element('window').bind('scroll',function(){
    //                alert(11)
    //            });
    //            elm.bind('scroll', function (e) {
    //                if (scope.hasMore) {
    //
    //                    if (raw.scrollTop + raw.offsetHeight >= raw.scrollHeight - 1) {
    //                        scope.$apply(attr.whenScrolled);
    //                    }
    //                }
    //            });
    //        }
    //    };
    //})
    .directive('infiniteScroll', [
        function () {
            function link(scope, element, attrs) {
                // 用jQuery重新包装当前元素，这样能定义滚动的命名空间
                var $element = $(element[0]);
                var raw = $element[0]
                $element.bind('scroll.my', function () {
                    if (raw.scrollTop + raw.offsetHeight >= raw.scrollHeight - scope.infinitScrollDistance) {
                        scope.$apply(scope.infinitScrollCallback());
                    }
                });
                scope.$watch('infinitScrollStatus', function (newValue, oldValue) {
                    if (newValue !== oldValue) {
                        if (!newValue) {
                            $element.unbind('scroll.my');
                        } else {
                            $element.bind('scroll.my', function () {
                                if (raw.scrollTop + raw.offsetHeight >= raw.scrollHeight - scope.infinitScrollDistance) {
                                    scope.$apply(scope.infinitScrollCallback());
                                }
                            });
                        }
                    }
                });
            }

            return {
                scope: {
                    infinitScrollStatus: '=',
                    infinitScrollCallback: '=',
                    infinitScrollDistance: '='
                },
                link: link
            }
        }
    ]);