<?php

$project_species = array(
    1 => array(
        'title' => 'Hudson Bay Project – Instructions and Species Identification Training',
        'background' => 'We are using trail cameras with time-lapse photography coupled with motion sensor triggers to document nesting events of Common Eiders and Snow Geese at La Peruse Bay within Wapusk National Park, near Churchill, Manitoba.  Your help with facilitate us knowing what predators are in the nesting colonies, when predators are arriving at nests, and how the birds are behaving throughout incubation (time when birds tend their eggs).  Your assistance will help us create an archive of images that we can then test different computer vision algorithms on as well as learn about the ecology of the system.',
        'task' => 'Locate animals in our time-lapse photography images by clicking on the image dragging the box over the animal.  Please try to make the boxes as small as possible while still containing all of the animal that you can see.',
        'identification' => 'Once you have selected an animal, use the drop down box to select which animal of interest it is.  If the animal name is not listed in the drop down box, please select “Other” and if known, please list the species name in the comments section.  If a bird appears to be on a nest, check the “on nest” box.  Select all animals that you can easily identify as a particular species. If the animal is so small it is hard to determine what species it is, you should omit not select it.',
        'examples' => array(
            array(
                'title' => 'Common Eider',
                'description' => 'Male Common Eider (in back right) and a female Common Eider (lower left).  The female is sitting on a nest.  She is usually the only one to incubate the nest so although you might see males hanging around initially at the onset of incubation, it is the female that will tend the eggs and hatched  young.',
                'image' => 'marshall_common_eider.png'
            ),
            array (
                'title' => 'Common Eider on Nest',
                'description' => 'Female Common Eider on her nest.',
                'image' => 'marshall_common_eider_nest.png'
            ),
            array (
                'title' => 'Snow Goose',
                'description' => 'Snow geese can be white, as pictured here, or a blue-phase (pictured below).',
                'image' => 'marshall_snow_goose.png'
            ),
            array (
                'title' => 'Snow Goose, Blue Phase',
                'description' => 'Snow geese can also be blue with a white head.',
                'image' => 'marshall_snow_goose_blue.png'
            ),
            array (
                'title' => 'Arctic Fox',
                'description' => 'Arctic Fox stealing an egg from an eider nest.  Although this species is snow white in the winter, they have patches of black apparent on their faces and bodies during the summer when are cameras are deployed.  They are a common nest predator for both snow geese and eiders.',
                'image' => 'marshall_arctic_fox.png'
            ),
            array (
                'title' => 'Canada Goose',
                'description' => 'Other species of geese, including Canada Geese, also nest in the region.',
                'image' => 'marshall_canada_geese.png'
            ),
            array (
                'title' => 'Caribou',
                'description' => 'Caribou also reside within Wapusk National Park.  They are generally not considered nest predators; however, they will consume eggs opportunistically.',
                'image' => 'marshall_caribou.png'
            ),
            array (
                'title' => 'Grizzly Bear',
                'description' => 'Grizzly Bears are large, brown bears.  They were previously absent from Manitoba until around 1989, but in recent years observations at Wapusk National Park have occurred and some of these have been at bird nests.',
                'image' => 'marshall_grizzly_bear.png'
            ),
            array (
                'title' => 'Gull',
                'description' => 'Gulls are often predators of nesting bird eggs, including those of eiders and snow geese.',
                'image' => 'marshall_gull.png'
            ),
            array (
                'title' => 'Polar Bear',
                'description' => 'Polar Bears appear to be coming ashore earlier each year and that time now coincides with nesting snow geese and eiders.  This is a polar bear in the middle of the eider colony.',
                'image' => 'marshall_polar_bear.png'
            ),
            array (
                'title' => 'Raven',
                'description' => 'A raven is a black bird similar to a crow.  They are common nest predators and scavengers.',
                'image' => 'marshall_crow.png'
            ),
            array (
                'title' => 'Sandhill Crane',
                'description' => 'Sandhill Cranes are tall birds that in some years have been found to be eating eggs of common eiders.',
                'image' => 'marshall_sandhill_crane.png'
            ),
            array (
                'title' => 'Wolverine',
                'description' => 'Wolverines are large weasels that are occasionally caught on our cameras.  They can destroy nests and eat eggs, but will also try to capture the incubating parents.',
                'image' => 'marshall_wolverine.png'
            ),
        )
    ),
    2 => array(
    ),
    3 => array(
        'title' => 'Hudson Bay Project - UAS Imagery Training',
        'background' => 'Our goal here is to estimate the density of nesting lesser snow geese on the Cape Churchill Peninsula, Manitoba, Canada.  Unmanned aircraft are able to collect huge amounts of imagery in a short amount of time, but that means we have a lot of data to sort through after the field season is done.  The advent of unmanned aircraft is promising for wildlife biology but we need to make sure that we are getting the science right first, that’s where you come in!',
        'task' => 'Locate individual geese and their nests in our UAS imagery by clicking on the image and dragging the box over the animal or nest. Please try to make the boxes as small as possible while still containing the entire object that you can see.',
        'identification' => 'There are two different color morphs of geese in La Perouse Bay, white and blue (see below for examples). When identifying geese, we want to know how many of each color you find! The white geese should be fairly straight forward, though the blue geese are much harder to find as they blend in with their surroundings. Note that the two colors don’t occur at a 1:1 ratio, so if you are finding many more white geese than blue, that’s ok!',
        'examples' => array (
            array (
                'title' => 'White Snow Geese',
                'description' => 'Two white geese. Note the tapered shape of these birds, indicating they are probably standing and not on a nest. (This will be more obvious when we compare to birds on a nest).',
                'image' => 'marshall_uas_snow_geese.png',
            ),
            array (
                'title' => 'Blue Snow Goose',
                'description' => 'A single blue snow goose (probably on a nest).',
                'image' => 'marshall_uas_blue_snow_goose.png',
            ),
            array (
                'title' => '',
                'description' => '',
                'image' => '',
                'isnote' => "We do not count geese that are in flight! We don't count them because they move very quickly as the UAV is taking imagery, and there is a high chance that they could show up in multiple images, leading to miscounts. (no picture, have not found one yet!)."
            ),
            array (
                'title' => 'Nests',
                'description' => 'Nest location data is an important metric for researchers as it allows easy calculations of nest density and helps us understand how birds are using different habitat types. Though it may seem straightforward at first, identifying a nest can be difficult for a number of reasons. Here are a few questions you should ask yourself when trying to identify a nest.',
                'image' => '',
                'issection' => true,
                'rename_species' => 'Scenario'
            ),
            array (
                'title' => 'Scenario 1',
                'description' => 'Does the habitat type make sense for a nest? Snow geese prefer to nest on raised hummocks of vegetation to keep their eggs out of cold water. There are also lots of small ponds and lakes in the area we work, and snow geese definitely do not nest in the water.',
                'image' => 'marshall_uas_land_water_no_nest.png',
                'fig_note' => 'There are different colors of land and water in this photo. See that there are two geese in the water and one on land. There are no nests in this photo.'
            ),
            array (
                'title' => 'Scenario 2',
                'description' => 'What is the shape of the bird? Recall earlier we mentioned that standing birds have more of a tapered look to them? This is their tail feathers clearly forming a distinct pointy shape. Sitting birds are more “puffed-out” and end up looking much more round shaped as a result.',
                'image' => 'marshall_uas_standing_vs_sitting.png',
                'fig_note' => 'The birds on the left are much more streamlined and are clearly standing birds. See the difference in the two birds on the right, these birds are more rounded and clearly sitting.'
            ),
            array (
                'title' => 'Scenario 3',
                'description' => 'Is there a visible nest ring around my sitting bird? So now that you have determined that this is a sitting goose in an area that could have a nest, how do you tell it is actually on a nest? In many cases, you should see a slight (or obvious) nest ring, a pile of vegetation that mother goose has accumulated around her eggs to keep out the cold air.',
                'image' => 'marshall_uas_nest_ring.png',
                'fig_note' => 'A white goose on her nest, red arrows pointing to the visible nest ring around her nest. Note the distinct difference in color of the nest ring when compared to the surrounding ground.'
            ),
            array (
                'title' => 'Scenario 4',
                'description' => 'Is the nesting pair visible? Often geese come in a nesting pair, the female sits on the nest while the attending male is often nearby keeping watch for predators or other geese intruding on their personal space. If you are hesitant on whether or not you are looking at a nest, the presence of an attending male can help you decide.',
                'image' => 'marshall_uas_nest_pair.png',
                'fig_note' => 'In this photo the bottom white goose is clearly sitting, but there is no visible nest ring. The bird near the top is standing, and likely an attending male. For that reason I would classify the bottom bird as a female on a nest.'
            ),
            array (
                'title' => '',
                'description' => '',
                'image' => '',
                'isnote' => "Classifying nests is much more difficult than just identifying birds. Not all nests have visible nest rings, not all females have attending males, blue geese are inherently difficult and you probably wont even see a nest if it doesn’t have a bird sitting on it. Because of this we want to give you flexibility in your decision making. If you are positive you have a nest, mark it as a high confident nest and be proud of your nest identification skills! If you are a little more hesitant but still feel it's a nest, you can mark it as a low confidence nest. This way, we will be sure not to miss anything, and will give us the best chance of estimating the true number and density of nests in our imagery!"
            ),
        )
    )
);
?>
