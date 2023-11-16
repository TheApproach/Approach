import React, { useState } from 'react'
import "../PageStyling.css"
import { useParams, useNavigate } from 'react-router'
import Slider from 'rc-slider';
import 'rc-slider/assets/index.css';
import data from "../../utils/skills.json"

const SkillsToLearnComponent = () => {

  const { id } = useParams()
  console.log(id)
  const skill = data.find((item) => item.id === id);

  const [experienceLevel, setExperienceLevel] = useState(25);
  const [level, setLevel] = useState("");

  const marks = {
    0: 'I want to learn',
    25: 'I know the basics',
    50: 'I am pretty good but I will still need some guidances',
    75: 'I am an expert',
  };

  const SetExpeLevel = (e) => {
    setExperienceLevel(e.target.value)
    if ( experienceLevel === 0 ) {
      return (setLevel("I want to learn"));
      
    } else if ( experienceLevel === 25 ) {
      return (setLevel("I know the basics"));
      
    } else if ( experienceLevel === 50 ) {
      return (setLevel("I am pretty good but I will still need some guidances"));
      
    } else if ( experienceLevel === 75 ) {
      return (setLevel("I am an expert"));
      
    } 

    
  }

  // SetExpeLevel(experienceLevel);

  const navigate = useNavigate();
  const Navigatetopage = () => navigate(`/TeamFinder/chooseteam?exp=${level}&skill=${skill.name}`)


  return (
    <div className='wrapper'> 
      <div className=' heading'>
        <h1>{skill.name}</h1>
      </div>
      <div className='span' >
        <span >Cool! How experienced are you with {skill.name}? </span>
      </div>
      <div className="container">
        <div className='slider-container'>
          <h3 className='h3'>Experience Level: {marks[experienceLevel]}</h3>
          <Slider
            min={0}
            max={75}
            step={25}
            value={experienceLevel}
            marks={marks}
            onChange={setExperienceLevel}
            activeDotStyle={{ borderColor: 'blue' }}
          />
        </div>

        <button onClick={() => Navigatetopage()} className='button-container'>
          <h3 className='h3'>Continue</h3>
        </button>
      </div>
      





    </div>
  )
}

export default SkillsToLearnComponent
