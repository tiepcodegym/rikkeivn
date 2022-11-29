'use strict';

/*window.chartColors = {
    red: 'rgb(255, 99, 132)',
    orange: 'rgb(255, 159, 64)',
    yellow: 'rgb(255, 205, 86)',
    green: 'rgb(75, 192, 192)',
    blue: 'rgb(54, 162, 235)',
    purple: 'rgb(153, 102, 255)',
    grey: 'rgb(201, 203, 207)'
};*/
(function (global) {
    var ColorArray = {
        colorValues: [
            'rgb(255, 102, 102)',
            'rgb(0, 230, 0)',
            'rgb(255, 148, 77)',
            'rgb(255, 230, 128)',
            'rgb(0, 0, 179)',
            'rgb(204, 0, 153)',
            'rgb(102, 102, 153)',
            'rgb(0, 255, 191)',

            'rgb(255, 26, 26)',
            'rgb(255, 102, 0)',
            'rgb(77, 255, 77)',
            'rgb(255, 214, 51)',
            'rgb(26, 26, 255)',
            'rgb(255, 51, 204)',
            'rgb(148, 148, 184)',
            'rgb(102, 255, 217)',

            'rgb(204, 0, 0)',
            'rgb(230, 92, 0)',
            'rgb(230, 184, 0)',
            'rgb(153, 255, 153)',
            'rgb(102, 102, 255)',
            'rgb(255, 128, 223)',
            'rgb(209, 209, 224)',
            'rgb(179, 255, 236)',
        ],
        color: function (index) {
            return this.colorValues[index % 24]; // 24 = length of color value
        },
    };
    global.ColorArray = ColorArray;
}(this));
