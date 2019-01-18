const chromedriver = require('chromedriver');

module.exports = {
    before: function (done) {
        chromedriver.start();

        done();
    },

    after: function (done) {
        chromedriver.stop();

        done();
    },
    
    'site_url' : "https://front-qas.mixcart.ru"
};