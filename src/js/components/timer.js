import ready from '../utils/ready';
import Countdown from 'easytimer.js';
import '../utils/request-next-animation-frame';

const ASSETS_URL = document.currentScript.getAttribute('data-assets-url');

class Timers {
  
  constructor() {
    
    this.timers = [];

    // create wrapper element
    this.el = document.createElement('div');
    this.el.className = 'kitchen-timers';
    document.body.append(this.el);
  }

  add(timer) {
    if (this.el.hasChildNodes) {
      this.el.insertBefore(timer.el, this.el.firstChild);
    } else {
      this.el.appendChild(timer.el);
    }

    this.timers.push(timer);
  }

  remove(timer) {    
    const i = this.timers.indexOf(timer);
    
    if (i === -1) {
      return;
    }

    this.timers[i].destroy().then(() => {
      this.detach();
    });
  }

}

class Timer {

  constructor(duration, title) {

    this.duration = this.parseDuration(duration);
    this.timer = null;

    title = typeof title === 'string' && title.length > 0 ?
      `<div class="kitchen-timer-title kitchen-timer-text">${title}</div>` :
      '';

    this.el = document.createElement('div');
    this.el.className = 'kitchen-timer';
    this.el.innerHTML = `
      <div class="kitchen-timer-content">
        <button class="kitchen-timer-playpause kitchen-timer-button">
          <svg class="kitchen-timer-icon-play" xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24"><g class="nc-icon-wrapper" stroke-linecap="square" stroke-linejoin="miter" stroke-width="2" fill="currentColor" stroke="currentColor"><polygon fill="none" stroke="currentColor" stroke-miterlimit="10" points="5,22 5,2 20,12 "></polygon></g></svg>
          <svg class="kitchen-timer-icon-pause" xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24"><g class="nc-icon-wrapper" stroke-linecap="square" stroke-linejoin="miter" stroke-width="2" fill="currentColor" stroke="currentColor"><rect x="3" y="2" fill="none" stroke="currentColor" stroke-miterlimit="10" width="6" height="20"></rect> <rect x="15" y="2" fill="none" stroke="currentColor" stroke-miterlimit="10" width="6" height="20"></rect></g></svg>
          <svg class="kitchen-timer-icon-restart" width="24" height="24" viewBox="0 0 24 24"><g fill="none" fill-rule="evenodd" stroke="currentColor" stroke-width="2" transform="matrix(-1 0 0 1 22 2)"><path d="M20,11 C20,16.5 15.5,21 10,21 C4.5,21 0,16.5 0,11 C0,5.5 4.5,1 10,1 C13.9,1 17.3,3.2 18.9,6.5"/><polyline stroke-linecap="square" points="19.8 .7 19 6.6 13 5.8"/></g></svg>
        </button>
        ${title}
        <div class="kitchen-timer-progress">
          <progress max="1" value="0"></progress>
        </div>
        <output class="kitchen-timer-remaining kitchen-timer-text">00:00</output>
        <button class="kitchen-timer-abort kitchen-timer-button">
        <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24"><g class="nc-icon-wrapper" stroke-linecap="square" stroke-linejoin="miter" stroke-width="2" fill="currentColor" stroke="currentColor"><line data-color="color-2" fill="none" stroke-miterlimit="10" x1="16" y1="8" x2="8" y2="16"></line> <line data-color="color-2" fill="none" stroke-miterlimit="10" x1="16" y1="16" x2="8" y2="8"></line> <circle fill="none" stroke="currentColor" stroke-miterlimit="10" cx="12" cy="12" r="11"></circle></g></svg>
        </button>
      </div>
    `;

    this.playPauseEl = this.el.querySelector('.kitchen-timer-playpause');
    this.progressEL  = this.el.querySelector('.kitchen-timer-progress progress');
    this.remainingEl = this.el.querySelector('.kitchen-timer-remaining');
    this.abortEl     = this.el.querySelector('.kitchen-timer-abort');

    this.isDestroying = false;

    this.playPauseEl.addEventListener('click', (e) => {
      e.preventDefault();
      if (this.timer.getTotalTimeValues().seconds === 0) {
        this.timer.reset();
        this.start();
      } else if (this.timer.isRunning()) {
        this.timer.pause();
        this.el.classList.remove('is-running');
      } else {
        this.timer.start();
        this.el.classList.add('is-running');
      }
    });

    this.abortEl.onclick = (e) => {
      e.preventDefault();
      this.destroy();
    };

    this.timer = new Countdown();
    
    this.timer.addEventListener('secondsUpdated', this.updateStatus.bind(this));

    const pling = new Audio(ASSETS_URL + '/sounds/pling.mp3');
    
    this.timer.addEventListener('targetAchieved', () => {
      this.el.classList.remove('is-running');
      this.el.classList.add('is-complete');
      pling.play();
    });
    
    this.start();
  }

  updateStatus() {
    requestAnimationFrame(() => {
      this.remainingEl.innerHTML =  this.timer.getTimeValues().toString();
      const secondsRemaining = this.timer.getTotalTimeValues().seconds;
      this.progressEL.value = 1 - secondsRemaining / this.duration;
    });
  }

  start() {
    this.timer.start({countdown: true, startValues: {seconds: this.duration}});
    this.updateStatus();
    this.el.classList.add('is-running');
    this.el.classList.remove('is-complete');

    requestNextAnimationFrame(() => {
      this.el.classList.add('is-visible');
    });
  }

  parseDuration(str) {

    let duration = 0;
    const pattern = /(\d+)(h|m|s)/g;
  
    let match;

    do {
        match = pattern.exec(str);
        if (match) {
            const val  = parseInt(match[1], 10);
            const unit = match[2];
            if (unit === 'h') {
              duration += val * 3600;
            } else if (unit === 'm') {
              duration += val * 60;
            } else if (unit === 's') {
              duration += val;
            }
        }
    } while (match);

    return duration;
  }

  destroy() {
    return new Promise((resolve) => {
      
      if (this.el.parentNode === null) {
        // already detached/destroyed
        resolve();
        return;
      }

      this.el.style.setProperty('--element-height', this.el.offsetHeight + 'px');
      this.el.classList.add('is-closing');

      setTimeout(() => {
        // leave some time for animation
        this.el.parentNode.removeChild(this.el);
        resolve();
      }, 350);
    });
  }
}


ready(() => {

  const timerButtons = document.querySelectorAll('[data-timer]');

  if(timerButtons.length === 0) {
    return;
  }

  const timers = new Timers();

  for(let i = 0, l = timerButtons.length; i < l; i++) {
    const title    = timerButtons[i].hasAttribute('data-timer-title') ? timerButtons[i].getAttribute('data-timer-title') : '';
    
    timerButtons[i].title = (title !== '' ? `Timer starten für ${title}` : 'Timer starten');
    
    timerButtons[i].addEventListener('click', (e) => {
      const duration = timerButtons[i].getAttribute('data-timer');
      e.preventDefault();
      timers.add(new Timer(duration, title));
    });

    timerButtons[i].setAttribute('role', 'button');
    timerButtons[i].setAttribute('tabindex', '0');
  }

});
