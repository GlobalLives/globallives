$(function() {
    $("#nav-featured .participant-thumbnail").click(function(){
        $(".participant-thumbnaill").removeClass("active")
        var a = $(this).attr("data-controls");
        var c = a.replace("content_","#location_"); // create the name of the map image from the data-controls field
        var e = $(c).attr("alt") // get the lat/long of the individual
        e = e.replace(" ",""); // replace the space from the alt tag with nothing so it can be put in an URL
        //---- add the src of the location image
        $(c).attr({"src":"http://maps.googleapis.com/maps/api/staticmap?center=" + e + "&zoom=6&size=600x400&markers=color:red|" + e + "&maptype=roadmap&sensor=false&style=feature:all%7Celement:geometry%7Csaturation:-100"})
    })
})