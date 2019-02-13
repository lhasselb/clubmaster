/* global window */

window.jQuery.entwine('ss', ($) => {
    $('.col-Sex').entwine({
        onmatch() {
            const sex = this.text().toLowerCase();
            /* eslint-disable no-console */
            if (sex === 'm') {
                // console.log(`sex = ${sex} add male`);
                // this.addClass('male');
                this.parent().addClass('male');
            } else if (sex === 'w') {
                // console.log(`sex = ${sex} add female`);
                // this.addClass('female');
                this.parent().addClass('female');
            }
            /* eslint-enable no-console */
        }
    });
});
