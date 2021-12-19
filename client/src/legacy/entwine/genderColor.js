/*
 * Add some color to table rows
 * */
window.jQuery.entwine('ss', ($) => {
  $('.col-Sex').entwine({
    onmatch() {
      const sex = this.text().toLowerCase();
      if (sex === 'm') {
        this.parent().addClass('male');
      } else if (sex === 'w') {
        this.parent().addClass('female');
      } else if (sex === 'd') {
        this.parent().addClass('divers');
      }
    }
  });
});
