define([], function () {
    require([], function () {
    //绑定data-toggle=addresspicker属性点击事件

    $(document).on('click', "[data-toggle='addresspicker']", function () {
        var that = this;
        var callback = $(that).data('callback');
        var input_id = $(that).data("input-id") ? $(that).data("input-id") : "";
        var lat_id = $(that).data("lat-id") ? $(that).data("lat-id") : "";
        var lng_id = $(that).data("lng-id") ? $(that).data("lng-id") : "";
        var lat = lat_id ? $("#" + lat_id).val() : '';
        var lng = lng_id ? $("#" + lng_id).val() : '';
        var url = "/addons/address/index/select";
        url += (lat && lng) ? '?lat=' + lat + '&lng=' + lng : '';
        Fast.api.open(url, '位置选择', {
            callback: function (res) {
                input_id && $("#" + input_id).val(res.address).trigger("change");
                lat_id && $("#" + lat_id).val(res.lat).trigger("change");
                lng_id && $("#" + lng_id).val(res.lng).trigger("change");
                try {
                    //执行回调函数
                    if (typeof callback === 'function') {
                        callback.call(that, res);
                    }
                } catch (e) {

                }
            }
        });
    });
});

require.config({
    paths: {
        'voicenotice': '../addons/voicenotice/js/voicenotice',
        'socket.io':['../addons/voicenotice/js/socket.io.min','https://cdn.bootcdn.net/ajax/libs/socket.io/2.4.0/socket.io.min']
    }
});


if (window.Config.actionname == "index" && window.Config.controllername == "index") {
    require(['voicenotice'], function (voicenotice) {
       if(typeof window.Config.voiceNotice!="undefined" &&  window.Config.voiceNotice.type=="socket.io"){
           require(["socket.io"],function (io) {
               var voiceNotice=window.Config.voiceNotice;
               var received=[];
               var socket = io(voiceNotice.url,{
                   "withCredentials": true,
                   "timestampRequests":false,
                   transports: ["websocket", "polling"],
                   query: {
                       token: voiceNotice.token
                   }
               });
               socket.on('connect', function(){
                   socket.on('tip', function(result){
                       console.log(result);
                   });
                   socket.on('notice', function(result){
                       if(result.state && received.indexOf(result.data.id)==-1){
                           result.data.text && received.push(result.data.id);
                           window.loop = typeof result.data.loop == 'boolean' ? true : parseInt(result.data.loop);
                           result.data.text && voicenotice.play(result.data);
                           result.data.loop != 'true' && socket.emit("received",result.data.id);
                       }
                   });
               });
               document.addEventListener('listen_stop', function(e){
                   if(e.val==true){
                       socket.emit("listen_stop",true)
                   }else{
                       socket.emit("listen_stop",false)
                   }
               }, false)

           })
           return false;
       }
        voicenotice.start();
    })
}
});