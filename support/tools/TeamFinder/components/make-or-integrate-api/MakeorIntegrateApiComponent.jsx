import React from 'react'
import "../PageStyling.css"
import { useNavigate } from 'react-router'

const MakeorIntegrateApiComponent = () => {
    const navigate = useNavigate();
    const Navigatetopage = (id) => navigate(`/TeamFinder/apis/${id}`)
    
    return (
        <div className='wrapper'>
            <div className=' heading'  >
                <h1>Make or Integrate Api</h1>
            </div>
            <div className='span' >
                <span >Great! what skills do you want to use and learn for this? </span>
            </div>

            <div className='container' >
                <div onClick={() => Navigatetopage(1)} className='button-container'><h3 className='h3'>Import and Sync via API</h3></div>
                <div onClick={() => Navigatetopage(2)} className='button-container' ><h3 className='h3'>Publishing with APIs</h3></div>
                <div onClick={() => Navigatetopage(3)} className='button-container'><h3 className='h3'>OIDC / OpenAPI / OAuth</h3></div>
                <div onClick={() => Navigatetopage(4)} className='button-container'><h3 className='h3'>Service Mesh Operation</h3></div>
                <div onClick={() => Navigatetopage(5)} className='button-container'><h3 className='h3'> Transcoders & Connectors</h3></div>
            </div>
        </div>

    )


}

export default MakeorIntegrateApiComponent
