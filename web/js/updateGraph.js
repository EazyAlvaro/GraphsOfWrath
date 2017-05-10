$(document).ready(function() {
        $('#sel1').on('change', function() {
            var year = this.value;
            $.ajax({
                url: '/sanne/books/' + year,
                success: function(data) {
                    bookData = data;
                    updateGraph(movieData, bookData, year);
                }
            });

            $.ajax({
                url: '/sanne/movies/' + year,
                success: function(data) {
                    movieData = data;
                    updateGraph(movieData, bookData, year);
                }
            });
        })
    }
);

var updateGraph = function(movieData, bookData, year) {

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
        downloadCanvas(this, 'myChart', year+'.png');
    }, false);

    var ctx = document.getElementById("myChart").getContext('2d');
    var data = {
        labels: ['jan', 'feb', 'march', 'april', 'may', 'june', 'july', 'aug', 'sep', 'oct', 'nov', 'dec'],
        datasets: [
            {
                borderWidth: 5,
                label: "Books ",
                data: bookData,
                borderColor: ['rgba(0,206,209,1)'],
                backgroundColor: ['rgba(0,206,209,0.5)']
            }, {
                label: ' Movies',
                data: movieData,
                borderColor: ['rgba(205,92,92,1)'],
                backgroundColor: ['rgba(205,92,92,0.5)']
            }
        ]
    };
    var chartInstance = new Chart(ctx, {
        type: 'line',
        data: data,
        options: {
            responsive: true,
            maintainAspectRation: false,
            title: {
                display: true,
                text: year ,
                padding: '1',
                fullWidth: true
            },
            scales: {
                yAxes: [{
                    ticks: {
                        stepSize: 1,
                        max: 15
                    }
                }]
            }
        }
    });
};
