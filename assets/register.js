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

const user_phone = document.querySelector('#user_phone');
const form = document.querySelector('form');
const uf = new UserPhone(user_phone);


user_phone.addEventListener('input' , () => {
    uf.country = uf.iti.getSelectedCountryData().iso2.toUpperCase();
    user_phone.value = uf.formatNumber();
});

form.addEventListener('formdata', (e) => {
    uf.prefix = uf.iti.getSelectedCountryData().dialCode;
  
    const formData = e.formData;
    formData.set('user[prefix]', uf.prefix);
    formData.set('user[country]', uf.country);
});




