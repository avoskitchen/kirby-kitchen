/**
 * Executes an optional callback function and always returns a promise, which resolves
 * once the DOM is ready.
 *
 * @param {function} fn An optional callback function.
 * @return {Promise} A promise which resolves once the DOM is ready.
 */
export default function ready(fn = function() {}) { /* eslint-disable-line no-empty-function */
  if(document.readyState !== 'loading') {
    fn();
  } else {
    document.addEventListener('DOMContentLoaded', () => {
      fn();
    });
  }
}
