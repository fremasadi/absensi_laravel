window.chart = function(config) {
    return new Chart(
        document.getElementById(config.id).getContext('2d'),
        {
            type: config.type,
            data: config.cachedData,
            options: config.options || {}
        }
    );
};