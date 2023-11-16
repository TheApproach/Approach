import React from 'react'
import { useLocation } from 'react-router-dom'

const SummaryPageComponent = ( ) => {
  // const location = useLocation();
  // const checkboxValue = location.state ? true : false;
  // console.log(checkboxValue)

  // console.log(checks)
  const locate = useLocation();
  const searchParams = new URLSearchParams(locate.search);
  const teamA = searchParams.get("teamA");
  const teamB = searchParams.get("teamB");
  const teamC = searchParams.get("teamC");
  const teamD = searchParams.get("teamD");
  const expLevel = searchParams.get("exp");
  const skillName = searchParams.get("skill")
    
  console.log(teamA, teamB, teamC)
  return (
    <div className='wrapper'>
      <div className="heading">
        <h1> Summary Page</h1>
      </div>
      <div className='span' >
        <span >Confirm the all your entries then submit </span>
      </div>
      <div className='container' >
        <h1>{teamA}</h1>
        <h1>{teamB}</h1>
        <h1>{teamC}</h1>
        <h1>{teamD}</h1>
        <h1>{expLevel}</h1>
        <h1>{skillName}</h1>
      </div>
      
    </div>
  )
}

export default SummaryPageComponent
 