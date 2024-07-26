function getHighScores()
{
    $("#instructions-text").fadeTo(500, 0, function() {
        $(this).html("Retrieving times...");
    }).fadeTo(500, 1);

    $.ajax({
        type: "POST",
        url: "DB.php",
        data: {
            func: 1
        },
        dataType: "json",
        timeout: 10000,
        //async: false,
        success: function(data) {
            numHighScores = data.length;

            for (var i = 0; i < data.length; i++)
                highScores[i] = data[i];
            
            $("#instructions-text").fadeTo(500, 0, function() {
                var html = "<table>";
                for (var i = 0; i < numHighScores; i++)
                {
                    html += "<tr><td>[" + (i+1).toString() + "] " + highScores[i].facebook_name + "</td><td>" + highScores[i].score_time + "</td></tr>";
                }
                html += "</table";
                $("#instructions-text").html(html);               
            }).fadeTo(500, 1, function() {
                rightTabState = RightTabState.HighScores;
            });
        },
        error: function(request, status, err) {
            $("#instructions-text").fadeTo(500, 0, function() {
                $("#instructions-text").html("There was an error retrieving times.");             
            }).fadeTo(500, 1, function() {
                rightTabState = RightTabState.HighScores;
            });
        }
    });
}

function showInstructions()
{
    if (rightTabState == RightTabState.HighScores)
    {
        rightTabState = RightTabState.InstructionsTransition;

        $("#instructions-text").fadeTo(500, 0, function() {
            $("#instructions-text").html(
                "1. Click on any two blue cards to see if the same friend appears." +
                "<br>2. If they match, your friend's name will appear in the list below." + 
                "<br>3. The timer begins as soon as you click the first card. Try to match them all as fast as you can!"
            );
        }).fadeTo(500, 1, function() {
            rightTabState = RightTabState.Instructions;
        });
        
        $("#instructions-header").html("How to play");
        $("#instructions-tab-left").removeClass("instructions-tab-left-inactive").addClass("instructions-tab-left-active");
        $("#instructions-tab-right").removeClass("instructions-tab-right-active").addClass("instructions-tab-right-inactive");
    }
}

function showHighScores()
{
    if (rightTabState == RightTabState.Instructions)
    {
        rightTabState = RightTabState.HighScoresTransition;

        getHighScores();

        $("#instructions-header").html("Best times");
        $("#instructions-tab-left").removeClass("instructions-tab-left-active").addClass("instructions-tab-left-inactive");
        $("#instructions-tab-right").removeClass("instructions-tab-right-inactive").addClass("instructions-tab-right-active");
    }
}

function showSubmitScore()
{
    if (rightTabState == RightTabState.Instructions || rightTabState == RightTabState.HighScores)
    {
        rightTabState = RightTabState.SubmitScore;
        
        $("#instructions-text").fadeTo(500, 0, function() {
            $("#instructions-text").html("Submitting your time...");
        }).fadeTo(500, 1);
        
        $("#instructions-header").html("Best times");
        $("#instructions-tab-left").removeClass("instructions-tab-left-active").addClass("instructions-tab-left-inactive");
        $("#instructions-tab-right").removeClass("instructions-tab-right-inactive").addClass("instructions-tab-right-active");
    }
}

function submitHighScore(facebook_id, facebook_name, score_time)
{
    showSubmitScore();

    $.ajax({
        type: "POST",
        url: "DB.php",
        data: {
            func: 2,
            facebook_id: facebook_id,
            facebook_name: facebook_name,
            score_time: score_time
        },
        dataType: "json",
        timeout: 10000,
        success: function(data) {
            if (data == false)
                alert("Error submiting time");
        },
        error: function(request, status, err) {
            alert("Error submiting time");
        },
        complete: function(request, status) {
            if (rightTabState == RightTabState.SubmitScore)
                getHighScores(); // already on the High Scores tab
            else
                showHighScores();
        }
    });
}

