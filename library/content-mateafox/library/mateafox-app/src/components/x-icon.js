import React from 'react'

const logo = 'url(https://raw.githubusercontent.com/darkwood-com/darkwood/main/library/darkwood-php/src/Darkwood/Bundle/DarkwoodBundle/assets/images/social/x.svg)'

export default function XIcon() {
  return (
    <span
      aria-hidden="true"
      style={{
        display: 'inline-block',
        width: '1em',
        height: '1em',
        backgroundColor: 'currentColor',
        mask: `${logo} center / contain no-repeat`,
        WebkitMask: `${logo} center / contain no-repeat`,
      }}
    />
  )
}
