import React, { useState } from 'react'
import "../PageStyling.css"
import { useNavigate } from 'react-router-dom'


const NewContributorPageComponent = () => {
    const navigate = useNavigate()

    const [ select, setSelect ] = useState();

    const Navigatetopage = (id) => {
        
        if (id === 33 ) {
           return navigate(`/TeamFinder/apis`)
        } else {
            return navigate(`/TeamFinder/apis/${id} `)
        }
        
    }
    return (

        <div className='wrapper'>
            <div className=' heading'  >
                <h1>Hello!</h1>
            </div>
            <div className='span' >
                <span >what kind of work would you like to do? </span>
            </div>

            <div className='container' >
                <button className='button-container' name='Designing Mockups' onClick={() => Navigatetopage(11)}><h3 className='h3'>Designing Mockups</h3></button>
                
                <button className='button-container' name='Layout & Components' onClick={() => Navigatetopage(22)}><h3 className='h3'>Layouts & Components</h3></button>

                <button className='button-container' name='Make or Integrate APIs' onClick={() => Navigatetopage(33)}><h3 className='h3'>Make or Integrate APIs</h3></button>

                <button className='button-container' name='DevOps, Ansible, Admin' onClick={() => Navigatetopage(44)}><h3 className='h3'>DevOps, Ansible, Admin</h3></button>

                <button className='button-container' name='Deep Engineering' onClick={() => Navigatetopage(55)}><h3 className='h3'>Deep Engineering</h3></button>

            </div>
        </div>

    )
}

export default NewContributorPageComponent
