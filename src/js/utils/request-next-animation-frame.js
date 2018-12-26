// requestNextAnimationFrame from: https://gist.github.com/getify/3004342
  var ids = {};

function requestId() {
  let id;
  do {
    id = Math.floor(Math.random() * 1E9); // 1E9 = 1,000,000,000
  } while (id in ids);
  return id;
}

if (!window.requestNextAnimationFrame) {
  window.requestNextAnimationFrame = (callback, element) => {
    var id = requestId();

    ids[id] = requestAnimationFrame(function () {
      ids[id] = requestAnimationFrame(function (ts) {
        delete ids[id];
        callback(ts);
      }, element);
    }, element);

    return id;
  };
}

if (!window.cancelNextAnimationFrame) {
  window.cancelNextAnimationFrame = (id) => {
    if (ids[id]) {
      cancelAnimationFrame(ids[id]);
      delete ids[id];
    }
  };
}
