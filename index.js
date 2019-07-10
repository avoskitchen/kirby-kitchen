panel.plugin("avoskitchen/kitchen", {

  fields: {

    // Based on the kirby-last-edited plugin by Dennis Kerzig
    // https://github.com/wottpal/kirby-last-edited/
    'kitchen-lastedited': {

      props: {
        value: String,
        modified: String,
        source: String
      },

      mounted() {
        // Convert Unix-Timestamp to "YYYY-MM-DD HH:MM:SS"
        const date = new Date(parseInt(this.modified) * 1000);

        const year = date.getFullYear();
        const month = ('0' + (date.getMonth() + 1)).slice(-2);
        const day = ('0' + date.getDate()).slice(-2);
        const hour = ('0' + date.getHours()).slice(-2);
        const minutes = ('0' + date.getMinutes()).slice(-2);
        const seconds = ('0' + date.getSeconds()).slice(-2);

        this.modified = `${year}-${month}-${day} ${hour}:${minutes}:${seconds}`;
      },

      template: `
        <k-date-field v-if="['metadata', 'field'].includes(this.source)" v-model="this.source == 'field' ? value : modified" v-bind="$attrs" ref="input" icon="clock" disabled="true" label:="$t('lastedited')" />
      `,
    },

    // Based on the Kirby 3 Janitor Plugin by Bruno Meilick
    // https://github.com/bnomei/kirby3-janitor
    'kitchen-ajaxbutton': {
      props: {
        label: String,
        progress: String,
        hideif: String,
        job: String,
        cooldown: Number,
      },

      data() {
        return {
          status: 'is-hidden',
        }
      },

      mounted() {
        if (typeof this.hideif !== 'undefined') {
          this.apiRequest(this.hideif).then((response) => {
            if(!response.result) {
              this.status = '';
            }
          });
        }
      },

      methods: {
        doJob() {
          this.getRequest(this.job);
        },

        getPageId() {
          return this.$attrs.endpoints.model.replace(/^pages\//, '');
        },

        apiRequest(action) {
          return this.$api.get(action + '?page=' + encodeURIComponent(this.getPageId()));
        },

        getRequest(action) {
          const oldlabel = this.label;
          const that = this;

          this.label = this.progress.length > 0 ? this.progress : this.label + ' …';
          this.status = 'is-doing-job';

          this.apiRequest(action).then((response) => {
              if (response.label !== undefined) {
                that.label = response.label;
              }

              if (response.status !== undefined) {
                that.status = response.status == 200 ? 'is-success' : 'has-error';
              } else {
                that.status = 'has-response';
              }

              if(response.hideButton !== true) {
                setTimeout(() => {
                  that.label = oldlabel;
                  that.status = '';
                }, that.cooldown);
              } else {
                window.location.reload(true);
              }
          });

        },
      },

      template: `
        <k-button class="kitchen-ajaxbutton" :class="status" @click="doJob()" :job="job">{{ label }}</k-button>
      `,
    },
  },
});




/**
 * Copyright (C) Amber Creative Lab Ltd
 *
 * Version 1, 2 July 2018
 *
 * Nucleo Icons
 *
 * https://nucleoapp.com/
 *
 * The Nucleo icons are copyrighted. Redistribution is not permitted. Use in
 * source and binary forms, with or without modification, is permitted only if
 * you possess a Nucleo icons license.
 *
 * Please refer to the license for additional information https://nucleoapp.com/license
 */
const sprite = document.querySelector('svg defs');
const icons  = `
<symbol viewBox="0 0 16 16" id="icon-kitchen-salad">
  <path d="M10,1A4,4,0,0,0,6,5h8A4,4,0,0,0,10,1Z" />
  <path d="M15,7H1A1,1,0,0,0,0,8,8,8,0,0,0,16,8,1,1,0,0,0,15,7Z"/>
  <path d="M4,5V4A4,4,0,0,0,0,0V1A4,4,0,0,0,4,5Z"/>
</symbol>
<symbol viewBox="0 0 16 16" id="icon-kitchen-scale">
  <path d="M0,0l0.544,1.632C0.816,2.449,1.581,3,2.442,3H7v2.08C3.609,5.566,1,8.474,1,12v3c0,0.552,0.448,1,1,1h12 c0.552,0,1-0.448,1-1v-3c0-3.526-2.609-6.434-6-6.92V3h4.558c0.861,0,1.625-0.551,1.897-1.368L16,0H0z M10,11c0,1.105-0.895,2-2,2 s-2-0.895-2-2c0-1.105,0.895-2,2-2S10,9.895,10,11z"/>
</symbol>
<symbol viewBox="0 0 16 16" id="icon-kitchen-world">
<path d="M8,16c-4.4,0-8-3.6-8-8s3.6-8,8-8s8,3.6,8,8S12.4,16,8,16z M8,2C4.7,2,2,4.7,2,8s2.7,6,6,6s6-2.7,6-6 S11.3,2,8,2z"></path> <path data-color="color-2" d="M4.2,8c0.3-0.8,1.6-1,2.2-0.7C7,7.6,7.5,7.3,8.1,8c0.5,0.7,1,0.5,1.1,1.5s-1.3,2.8-1.6,3.3 c-0.4,0.5-1.4-0.1-1.4-1.5S5.5,9.8,5.1,9.7C4.6,9.5,3.6,9.4,4.2,8z"></path> <path data-color="color-2" d="M6.7,3.5c0,0-0.2,0.5,0.2,0.6c1,0.2,1.1,0.5,1.5,1c0.4,0.5-0.1,1-0.2,1.6 C8.1,7.2,8.5,7.5,9.4,7.3c1.2-0.3,1.3,1,2,0.9c0.6-0.1,1.1,0.9,1.4,1C12.9,8.8,13,8.4,13,8c0-1.9-1.1-3.5-2.6-4.4 C10,3.5,9.6,3.5,9.2,3.5C8.4,3.5,7.7,3,6.7,3.5z"/>
</symbol>
<symbol viewBox="0 0 16 16" id="icon-kitchen-cookbook">
<path d="M14,0H2C1.4,0,1,0.4,1,1v14c0,0.6,0.4,1,1,1h12c0.6,0,1-0.4,1-1V1C15,0.4,14.6,0,14,0z M4,13 c-0.6,0-1-0.4-1-1c0-0.6,0.4-1,1-1s1,0.4,1,1C5,12.6,4.6,13,4,13z M4,9C3.4,9,3,8.6,3,8c0-0.6,0.4-1,1-1s1,0.4,1,1C5,8.6,4.6,9,4,9z M4,5C3.4,5,3,4.6,3,4c0-0.6,0.4-1,1-1s1,0.4,1,1C5,4.6,4.6,5,4,5z M13,7H7V4h6V7z"/>
</symbol>
<symbol viewBox="0 0 16 16" id="icon-kitchen-library">
<path d="M6,12h2c0.6,0,1-0.4,1-1V3.5l3.2,7.9c0.2,0.5,0.8,0.8,1.3,0.5l1.9-0.8c0.5-0.2,0.8-0.8,0.5-1.3l-3.8-9.3 c-0.2-0.5-0.8-0.8-1.3-0.5L9,0.8c0,0,0,0,0,0C8.9,0.4,8.5,0,8,0H6C5.4,0,5,0.4,5,1v10C5,11.6,5.4,12,6,12z"></path> <path d="M1,12h2c0.6,0,1-0.4,1-1V1c0-0.6-0.4-1-1-1H1C0.4,0,0,0.4,0,1v10C0,11.6,0.4,12,1,12z"></path>
<rect data-color="color-2" y="14" width="16" height="2"></rect>
</symbol>
<symbol viewBox="0 0 16 16" id="icon-kitchen-lock">
<path d="M8,0C5.8,0,4,1.8,4,4v1H2C1.4,5,1,5.4,1,6v9c0,0.6,0.4,1,1,1h12c0.6,0,1-0.4,1-1V6c0-0.6-0.4-1-1-1h-2V4 C12,1.8,10.2,0,8,0z M9,11.7V13H7v-1.3c-0.6-0.3-1-1-1-1.7c0-1.1,0.9-2,2-2s2,0.9,2,2C10,10.7,9.6,11.4,9,11.7z M10,5H6V4 c0-1.1,0.9-2,2-2s2,0.9,2,2V5z"></path>
</symbol>
`;

sprite.insertAdjacentHTML('beforeend', icons);


