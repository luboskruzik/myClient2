console.log('register');

import intlTelInput from 'intl-tel-input'

import '../node_modules/intl-tel-input/build/js/utils'

import parsePhoneNumber from 'libphonenumber-js'

import { AsYouType } from 'libphonenumber-js'


class UserPhone {
    
    country = null;
    prefix = null;
    
    constructor(user_phone) {
        this.iti = intlTelInput(user_phone, {
            initialCountry: 'CZ',
            utilsScript: '' // this is a default and it's a path to the imported script utils.js
        });
    }
    
    formatNumber() {
        const number = this.iti.getNumber();
        const input = new AsYouType(this.country).input(number);
        return input;
    }
}

const user_phone = document.querySelector('#register_user_phone');
const form = document.querySelector('form');
const up = new UserPhone(user_phone);


user_phone.addEventListener('input' , () => {
    user_phone.value = up.formatNumber();
});

form.addEventListener('formdata', (e) => {
    up.prefix = up.iti.getSelectedCountryData().dialCode;
    up.country = up.iti.getSelectedCountryData().iso2.toUpperCase();
  
    const formData = e.formData;
    formData.set('register_user[prefix]', up.prefix);
    formData.set('register_user[country]', up.country);
});
