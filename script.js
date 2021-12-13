var colours = [
	'#f00',
	'#0f0',
	'#00f',
	'#cc0',
	'#0ff',
	'#f0f',
	'#000',
];

var hosts = [];

var data = [];

var config = {
	type: 'line',
	data: {
		datasets: []
	},
	options: {
		maintainAspectRatio: false,
		animation: {
			duration: 0
		},
		scales: {
			x: {
				type: 'time',
				title: {
					display: true,
					text: 'time, your browser\'s timezone',
				}
			},
			y: {
				suggestedMax: 1,
				min: 0,
				title: {
					display: true,
					text: 'load average divided by number of cpu threads; greater than 1 is overload',
				}
			}
		},
		elements: {
			line: {
				tension: 0.2,
			},
			point: {
				borderWidth: 0,
				backgroundColor: 'transparent',
			}
		},
		plugins: {
			autocolors: false,
			annotation: {
				annotations: {
					overload: {
						type: 'line',
						yMin: 1,
						yMax: 1,
						borderColor: '#f00',
						borderWidth: 2,
					},
					warning: {
						type: 'line',
						yMin: 0.7,
						yMax: 0.7,
						borderColor: '#cc0',
						borderWidth: 2,
					}
				}
			}
		}
	}
};

const myChart = new Chart(
	document.getElementById('loadAverage'),
	config
);

updateChart();
window.onresize = resizeScreen;
setInterval(updateChart, 5000);

function updateChart() {
	const select = document.querySelector('select');
	const average = select.options[select.selectedIndex].value;

	fetch('data.php?topic=' + average + '&_=' + Date.now()).then(response => {
		if (!response.ok) {
			throw Error(response.statusText);
		}

		return response.json();
	}).then(data => {
		window.hosts = data.hosts;

		selectAverage();

		for (dataset in window.config.data.datasets) {
			dataset = window.config.data.datasets[dataset];
			dataset.data = data.chart
		};

		// myChart.update();
		selectAverage();
	}).catch(error => {
		alert('Error: ' + error);
	});
}

function selectAverage() {
	const select = document.querySelector('select');
	const average = select.options[select.selectedIndex].value;

	var data = window.config.data.datasets.length ? window.config.data.datasets[0].data : [];

	window.config.data.datasets = [];
	for (hostname in window.hosts) {
		hostname = window.hosts[hostname];

		window.config.data.datasets.push({
			label: hostname,
			data: data,
			borderColor: window.colours[window.hosts.indexOf(hostname)],
			backgroundColor: 'transparent',
			parsing: {
				yAxisKey: hostname + '/' + average,
			}
		});
	};

	// myChart.update();
	resizeScreen();
}

function resizeScreen() {
	const select = document.querySelector('select');
	const average = select.options[select.selectedIndex].value;
	const multiplier = average.substr(0, average.length - 1);

	myChart.options.scales.x.min = undefined;//Date.now() - (5000 * multiplier * getScreenWidth());
	myChart.options.scales.x.max = undefined;//Date.now();
	myChart.update();
}

function getScreenWidth() {
	return Math.max(
		document.body.scrollWidth,
		document.documentElement.scrollWidth,
		document.body.offsetWidth,
		document.documentElement.offsetWidth,
		document.documentElement.clientWidth
	);
}
