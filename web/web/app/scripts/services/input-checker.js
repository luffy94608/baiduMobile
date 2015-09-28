'use strict';

angular.module('weChatHrApp')
    .service('inputChecker', ['string',function inputChecker(string) {
        this.limit = {
            occup:{
                company_name:{
                    min:1,
                    max:40,
                    key_word:string.COMPANY
                },
                position:{
                    min:2,
                    max:30,
                    key_word:string.POSITION
                },
                desc:{
                    min:0,
                    max:500,
                    key_word:string.DESC
                }
            },
            edu:{
                school:{
                    min:1,
                    max:50,
                    key_word:string.SCHOOL
                },
                department:{
                    min:0,
                    max:50,
                    key_word:string.DEPARTMENT
                },
                major:{
                    min:1,
                    max:50,
                    key_word:string.MAJOR
                },
                desc:{
                    min:0,
                    max:500,
                    key_word:string.DESC
                }
            },
            project:{
                name:{
                    min:1,
                    max:20,
                    key_word:string.PROJECT_NAME
                },
                role:{
                    min:1,
                    max:500,
                    key_word:string.ROLE
                },
                desc:{
                    min:0,
                    max:500,
                    key_word:string.PROJECT_DESC
                },
                website:{
                    min:0,
                    max:200,
                    key_word:string.PROJECT_WEBSITE,
                    regex:/^(https?:\/\/)?(((([a-z]|\d|-|\.|_|~|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF])|(%[\da-f]{2})|[!\$&'\(\)\*\+,;=]|:)*@)?(((\d|[1-9]\d|1\d\d|2[0-4]\d|25[0-5])\.(\d|[1-9]\d|1\d\d|2[0-4]\d|25[0-5])\.(\d|[1-9]\d|1\d\d|2[0-4]\d|25[0-5])\.(\d|[1-9]\d|1\d\d|2[0-4]\d|25[0-5]))|((([a-z]|\d|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF])|(([a-z]|\d|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF])([a-z]|\d|-|\.|_|~|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF])*([a-z]|\d|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF])))\.)+(([a-z]|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF])|(([a-z]|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF])([a-z]|\d|-|\.|_|~|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF])*([a-z]|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF])))\.?)(:\d*)?)(\/((([a-z]|\d|-|\.|_|~|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF])|(%[\da-f]{2})|[!\$&'\(\)\*\+,;=]|:|@)+(\/(([a-z]|\d|-|\.|_|~|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF])|(%[\da-f]{2})|[!\$&'\(\)\*\+,;=]|:|@)*)*)?)?(\?((([a-z]|\d|-|\.|_|~|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF])|(%[\da-f]{2})|[!\$&'\(\)\*\+,;=]|:|@)|[\uE000-\uF8FF]|\/|\?)*)?(\#((([a-z]|\d|-|\.|_|~|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF])|(%[\da-f]{2})|[!\$&'\(\)\*\+,;=]|:|@)|\/|\?)*)?$/i
                }
            },
            skill:{
                name:{
                    min:1,
                    max:20,
                    key_word:string.SKILL
                }
            },
            contact:{
                email:{
                    min:1,
                    max:-1,
                    key_word:string.EMAIL,
                    regex:/^[a-z0-9]+([._-]*[a-z0-9]+)*@([a-z0-9\-_]+([.\_\-][a-z0-9]+))+$/i

                },
                mobile:{
                    min:1,
                    max:20,
                    key_word:string.PHONE_NUMBER,
                    regex:/^(\+|00)?[0-9\s\-]{5,20}$/
                },
                postcode:{
                    min:0,
                    max:20,
                    key_word:string.POST_CODE
                },
                qq:{
                    min:0,
                    max:20,
                    key_word:string.QQ
                },
                wechat:{
                    min:0,
                    max:20,
                    key_word:string.WECHAT
                },
                website:{
                    min:0,
                    max:200,
                    key_word:string.WEBSITE,
                    regex:/^(https?:\/\/)?(((([a-z]|\d|-|\.|_|~|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF])|(%[\da-f]{2})|[!\$&'\(\)\*\+,;=]|:)*@)?(((\d|[1-9]\d|1\d\d|2[0-4]\d|25[0-5])\.(\d|[1-9]\d|1\d\d|2[0-4]\d|25[0-5])\.(\d|[1-9]\d|1\d\d|2[0-4]\d|25[0-5])\.(\d|[1-9]\d|1\d\d|2[0-4]\d|25[0-5]))|((([a-z]|\d|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF])|(([a-z]|\d|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF])([a-z]|\d|-|\.|_|~|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF])*([a-z]|\d|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF])))\.)+(([a-z]|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF])|(([a-z]|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF])([a-z]|\d|-|\.|_|~|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF])*([a-z]|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF])))\.?)(:\d*)?)(\/((([a-z]|\d|-|\.|_|~|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF])|(%[\da-f]{2})|[!\$&'\(\)\*\+,;=]|:|@)+(\/(([a-z]|\d|-|\.|_|~|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF])|(%[\da-f]{2})|[!\$&'\(\)\*\+,;=]|:|@)*)*)?)?(\?((([a-z]|\d|-|\.|_|~|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF])|(%[\da-f]{2})|[!\$&'\(\)\*\+,;=]|:|@)|[\uE000-\uF8FF]|\/|\?)*)?(\#((([a-z]|\d|-|\.|_|~|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF])|(%[\da-f]{2})|[!\$&'\(\)\*\+,;=]|:|@)|\/|\?)*)?$/i
                },
                blog:{
                    min:0,
                    max:200,
                    key_word:string.BLOG,
                    regex:/^(https?:\/\/)?(((([a-z]|\d|-|\.|_|~|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF])|(%[\da-f]{2})|[!\$&'\(\)\*\+,;=]|:)*@)?(((\d|[1-9]\d|1\d\d|2[0-4]\d|25[0-5])\.(\d|[1-9]\d|1\d\d|2[0-4]\d|25[0-5])\.(\d|[1-9]\d|1\d\d|2[0-4]\d|25[0-5])\.(\d|[1-9]\d|1\d\d|2[0-4]\d|25[0-5]))|((([a-z]|\d|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF])|(([a-z]|\d|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF])([a-z]|\d|-|\.|_|~|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF])*([a-z]|\d|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF])))\.)+(([a-z]|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF])|(([a-z]|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF])([a-z]|\d|-|\.|_|~|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF])*([a-z]|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF])))\.?)(:\d*)?)(\/((([a-z]|\d|-|\.|_|~|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF])|(%[\da-f]{2})|[!\$&'\(\)\*\+,;=]|:|@)+(\/(([a-z]|\d|-|\.|_|~|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF])|(%[\da-f]{2})|[!\$&'\(\)\*\+,;=]|:|@)*)*)?)?(\?((([a-z]|\d|-|\.|_|~|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF])|(%[\da-f]{2})|[!\$&'\(\)\*\+,;=]|:|@)|[\uE000-\uF8FF]|\/|\?)*)?(\#((([a-z]|\d|-|\.|_|~|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF])|(%[\da-f]{2})|[!\$&'\(\)\*\+,;=]|:|@)|\/|\?)*)?$/i
                },
                address:{
                    min:0,
                    max:200,
                    key_word:string.ADDRESS
                }

            },
            subscribe:{
                title:{
                    min:1,
                    max:30,
                    key_word:string.POSITION
                }
            },
            basic:{
                name:{
                    min:2,
                    max:20,
                    key_word:string.NAME,
                    regex:/^[\u4E00-\u9FA5a-zA-Z\s·]+$/,
                    un_regex:/^（）[！？。，《》{}【】“”·、：；‘’……]+$/
                }
            },
//            job_objective:{
//                position:{
//                    min:1,
//                    max:30,
//                    key_word:string.POSITION
//                }
//            },
            evaluate:{
                evaluate:{
                    min:0,
                    max:500,
                    key_word:string.EVALUATE
                }
            },
            search:{
                keyword:{
                    min:1,
                    max:50,
                    key_word:string.KEY_WORD
                }
            }
        }
        this.check = function(model,key,value,error){
            if(!error){
                error = {desc:''};
            }
            var config = this.limit[model][key];
            if(config){
                if(!value){
                    if(config.min <= 0){
                        return true;
                    }else{
                        error.desc = string.CHECK_EMPTY.format(config.key_word);
                        return false;
                    }
                }
                if(value.length < config.min ){
                    if(1 === config.min){
                        error.desc = string.CHECK_EMPTY.format(config.key_word);
                        return false;
                    }else{
                        error.desc = string.CHECK_MIN.format(config.key_word,config.min);
                        return false;
                    }
                }
                if(-1 !== config.max && value.length > config.max){
                    error.desc = string.CHECK_MAX.format(config.key_word,config.max);
                    return false;
                }
                if((config.regex && !value.toString().match(config.regex))||(config.un_regex && value.toString().match(config.un_regex))){
                    error.desc = string.CHECK_CHARACTER.format(config.key_word);
                    return false;
                }
            }
            return true;
        }
        this.getMax = function(model,key){
            return this.limit[model][key].max;
        }

    }]);
