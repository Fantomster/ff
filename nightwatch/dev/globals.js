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

    site_url: "https://front-dev.mixcart.ru",
    credentials: {
        email: 'bigle6732@gmail.com',
        password: 'QWEasd123'
    },
    dev_1628: {
        test1: 'Ваш бизнес-профиль',
        test2: 'Betsy',
        test3: 'Добро пожаловать'
    }
};