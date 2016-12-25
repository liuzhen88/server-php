if (typeof AGG == "undefined") {
    var AGG = {};
}


AGG.imageBlur = function(element, src, strength){
    var image = new Image();
    image.onload = function(e){
        var canvas = document.createElement('canvas');
        var context = canvas.getContext('2d');

        canvas.width = this.width;
        canvas.height = this.height;

        context.drawImage(this, 0, 0);

        context.globalAlpha = 0.5; // Higher alpha made it more smooth
        // Add blur layers by strength to x and y
        // 2 made it a bit faster without noticeable quality loss
        for (var y = -strength; y <= strength; y += 2) {
            for (var x = -strength; x <= strength; x += 2) {
                context.drawImage(canvas, x, y);
            }
        }
        context.globalAlpha = 1;
        element.style.backgroundImage = 'url('+canvas.toDataURL()+')';
    };
    image.setAttribute('crossOrigin', '*');
    image.src = src;
}



//image高斯模糊的方法
//AGG.imageBlur(document.getElementById('bgtest'),'../../images/share_personal_bg.jpg',2);