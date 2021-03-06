var updateTotalsGraphs = function(dataSets, labels, stepSize) {

    /**
     * Original canvas download snippet thanks to
     * Ken Fyrstenberg Nilsen
     * https://jsfiddle.net/AbdiasSoftware/7PRNN/
     */

    /**
     * This is the function that will take care of image extracting and
     * setting proper filename for the download.
     * IMPORTANT: Call it from within a onclick event.
     */
    function downloadCanvas(link, canvasId, filename) {
        link.href = document.getElementById(canvasId).toDataURL();
        link.download = filename;
    }

    /**
     * The event handler for the link's onclick event. We give THIS as a
     * parameter (=the link element), ID of the canvas and a filename.
     */
    document.getElementById('download').addEventListener('click', function () {
        downloadCanvas(this, 'myChart', 'all.png');
    }, false);

    if(labels == null) {
        labels = [2009,2010,2011,2012,2013,2014,2015,2016,2017];
    }


    var ctx = document.getElementById("myChart").getContext('2d');
    var data = {
        labels: labels,
        datasets: dataSets

    };

    var chartInstance = new Chart(ctx, {
        type: 'line',
        data: data,
        options: {
            responsive: true,
            maintainAspectRation: false,
            title: {
                display: true,
                text: 'Yearly Totals' ,
                padding: '1',
                fullWidth: true
            },
            scales: {
                yAxes: [{
                    ticks: {
                        stepSize: stepSize,
                    }
                }]
            }
        }
    });
};
