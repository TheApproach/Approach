import React, { Children, Component, useState } from 'react'
import "../PageStyling.css"
import { useNavigate } from 'react-router'
import { Link, } from 'react-router-dom';
import { useLocation } from 'react-router-dom';
import SummaryPageComponent from '../summary-page/SummaryPageComponent';





const TeamComponent = () => {

    const navigate = useNavigate();
    
    const locate = useLocation();
    const searchParams = new URLSearchParams(locate.search);
    const expLevel = searchParams.get('exp');
    const skillName = searchParams.get('skill');

    const [ checkbox1, setCheckbox1 ] = useState(false);
    const [ checkbox2, setCheckbox2 ] = useState(false);
    const [ checkbox3, setCheckbox3 ] = useState(false);
    const [ checkbox4, setCheckbox4 ] = useState(false);

    const [teamA, setTeamA] = useState("");
    const [teamB, setTeamB] = useState("");
    const [teamC, setTeamC] = useState("");
    const [teamD, setTeamD] = useState("");

    

    const HandleChange1 = (e) => {
        e.preventDefault();
        setCheckbox1(e.target.checked)
        if (checkbox1 === true) {
           return setTeamA(e.target.name)
        } 

    }


    const HandleChange2 = (e) => {
        e.preventDefault();
        setCheckbox2(e.target.checked)
        if (checkbox1 === true) {
           return setTeamB(e.target.name)
        } 

    }

    const HandleChange3 = (e) => {
        e.preventDefault();
        setCheckbox3(e.target.checked)
        if (checkbox1 === true) {
           return setTeamC(e.target.name)
        } 

    }

    const HandleChange4 = (e) => {
        e.preventDefault();
        setCheckbox4(e.target.checked)
        if (checkbox1 === true) {
           return setTeamD(e.target.name)
        } 

    }

    
    const HandleSubmit = () => {
        console.log(`teamA: ${teamA}`)
        navigate( `/TeamFinder/chooseteam/summary?teamA=${teamA}&teamB=${teamB}&teamC=${teamC}&teamD=${teamD}&expLevel=${expLevel}&skill=${skillName}`);
    };

    console.log(teamA, teamB, teamC, teamD)
    

  return (
    <div className='wrapper'>
        <div className=' heading'  >
            <h1>Project Selection</h1>
        </div>
        <div className='span' >
            <span >Thanks for letting us know. We have these projects going on that could use your help! Pick up to 3 A team leader willl contact you soon</span>
        </div>

        <div className='container' >
            <div>
                <form onSubmit={HandleSubmit}>
                    <div className="checkbox-container">
                        <label className='h3'>
                            <input
                            type="checkbox"
                            name='Team Paladin: Zapier API'
                            checked={checkbox1}
                            onChange={HandleChange1}
                            />
                            Team Paladin: Zapier API
                        </label>
                    </div>
                    <div className="checkbox-container">
                        <label className='h3'>
                            <input
                            type="checkbox"
                            name='Team Coolname: Twitter API'
                            checked={checkbox2}
                            onChange={HandleChange2}
                            />
                            Team Coolname: Twitter API
                        </label>
                    </div>
                    <div className="checkbox-container">
                        <label className='h3'>
                            <input
                            type="checkbox"
                            name='Team Phoenix: Service Mesh'
                            checked={checkbox3}
                            onChange={HandleChange3}
                            />
                            Team Phoenix: Service Mesh
                        </label>
                    </div>
                    <div className="checkbox-container">
                        <label className='h3'>
                            <input
                            type="checkbox"
                            name='Team MyTeam: DNS API'
                            checked={checkbox4}
                            onChange={HandleChange4}
                            />
                            Team MyTeam: DNS API
                        </label>
                    </div>
                    

                    
                    <button type="submit" onClick={HandleSubmit} className='button-container'><h3 className='h3'>Continue</h3></button>
                    
                </form>
            
            </div>

            
            
            
        </div>
    </div>
  )
}

export default TeamComponent
