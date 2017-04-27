$(function() {
    $("#nav-featured .participant-thumbnail").click(function(){
        var a = $(this).attr("data-controls");
        var b = a.replace('content_','#');
        var c = a.replace('content_','#location_');
        var d = a.replace('content_','#video_');
        var e = $(c).attr("alt")
        e = e.replace(" ","");
        $.ajax({
            success: function( data ) {
                
            }
        })
        $(c).attr({'src':"http://maps.googleapis.com/maps/api/staticmap?center=" + e + "&zoom=6&size=600x400&markers=color:red|" + e + "&maptype=roadmap&sensor=false&style=feature:all%7Celement:geometry%7Csaturation:-100"})
    })
})