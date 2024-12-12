import { parsePhoneNumberFromString } from 'libphonenumber-js'

export function validateBVN(bvn) {
  const errors = []
  
  if (!bvn) {
    errors.push('BVN is required')
  } else if (!/^\d{11}$/.test(bvn)) {
    errors.push('BVN must be exactly 11 digits')
  }

  return {
    isValid: errors.length === 0,
    errors
  }
}

export function validateNIN(nin) {
  const errors = []
  
  if (!nin) {
    errors.push('NIN is required')
  } else if (!/^\d{11}$/.test(nin)) {
    errors.push('NIN must be exactly 11 digits')
  }

  return {
    isValid: errors.length === 0,
    errors
  }
}

export function validatePhoneNumber(phone) {
  const errors = []
  
  if (!phone) {
    errors.push('Phone number is required')
  } else {
    const phoneNumber = parsePhoneNumberFromString(phone, 'NG')
    if (!phoneNumber || !phoneNumber.isValid()) {
      errors.push('Please enter a valid Nigerian phone number')
    }
  }

  return {
    isValid: errors.length === 0,
    errors
  }
}

export function validateName(name, field = 'Name') {
  const errors = []
  
  if (!name) {
    errors.push(`${field} is required`)
  } else if (!/^[a-zA-Z\s'-]{2,50}$/.test(name)) {
    errors.push(`${field} must contain only letters, spaces, hyphens, and apostrophes`)
  }

  return {
    isValid: errors.length === 0,
    errors
  }
}