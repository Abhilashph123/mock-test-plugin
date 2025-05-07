jQuery(document).ready(function ($) {
    // Clear selected answer for the current question
    $(document).on('click', '#mock-clear-question-btn', function (e) {
        e.preventDefault();
        var $currentQuestion = $('.mock-question:visible');
        $currentQuestion.find('input[type="radio"]:checked').prop('checked', false);

        var currentQuestionIndex = $('.mock-question').index($currentQuestion);
        var $link = $('.mock-question-link').eq(currentQuestionIndex);
        if ($link.hasClass('answered')) {
            $link.removeClass('answered').css({
                'background-color': '#b55824',
                'color': '#fff',
                'border': '1px solid #b55824',
                'border-radius': '5px',
                'transform': 'perspective(200px) rotateY(30deg)',
            });
        }
    });

    var lastViewedQuestionIndex = 0;

    // Next button functionality
    $(document).on('click', '#mock-next-question-butn', function (e) {
        e.preventDefault();

        // Find the currently visible question
        var $currentQuestion = $('.mock-question:visible');
        var currentQuestionIndex = $('.mock-question').index($currentQuestion);

        var $link = $('.mock-question-link').eq(currentQuestionIndex);

        // Only apply styles if the link is not already 'answered' or 'marked'
        if (!$link.hasClass('answered') && !$link.hasClass('marked')) {
            $link.css({
                'background-color': '#b55824',
                'color': '#fff',
                'border': '1px solid #b55824',
                'transform': 'perspective(200px) rotateY(30deg)',
            }).addClass('answered').removeClass('active');
        }

        // Move to the next question
        var $nextQuestion = $currentQuestion.next('.mock-question');

        if ($nextQuestion.length > 0) {
            $currentQuestion.hide();
            $nextQuestion.show();

            var nextQuestionIndex = $('.mock-question').index($nextQuestion);
            $('#mock-save-next-question-btn').data('index', nextQuestionIndex);
            $('#mock-save-and-mark-question-btn').data('index', nextQuestionIndex);

            lastViewedQuestionIndex = nextQuestionIndex;
        }

        toggleNextPrevButtons();
    });

    // Previous button functionality
    $(document).on('click', '#mock-previous-question-butn', function (e) {
        e.preventDefault();

        var $currentQuestion = $('.mock-question:visible');
        var $prevQuestion = $currentQuestion.prev('.mock-question');

        if ($prevQuestion.length > 0) {
            $currentQuestion.hide();
            $prevQuestion.show();

            lastViewedQuestionIndex = $('.mock-question').index($prevQuestion);
        }

        toggleNextPrevButtons();
    });

    // Toggle Next/Previous buttons based on current question
    function toggleNextPrevButtons() {
        var $currentQuestion = $('.mock-question:visible');

        if ($currentQuestion.next('.mock-question').length > 0) {
            $('#mock-next-question-butn').show();
        } else {
            $('#mock-next-question-butn').hide();
        }

        if ($currentQuestion.prev('.mock-question').length > 0) {
            $('#mock-previous-question-butn').show();
        } else {
            $('#mock-previous-question-butn').hide();
        }
    }

    // Initial setup: Show only the first question and hide the Previous button
    $(document).ready(function () {
        $('.mock-question').hide();
        $('.mock-question:first').show();
        toggleNextPrevButtons();
    });

    // Click handler for question links
    $(document).on('click', '.mock-question-link', function (e) {
        e.preventDefault();

        var questionId = $(this).data('question-id');
        var $questionDiv = $('.mock-question-' + questionId);

        // Apply styles to the last viewed question link (if it's not already answered or marked)
        var $lastViewedLink = $('.mock-question-link').eq(lastViewedQuestionIndex);
        if (!$lastViewedLink.hasClass('answered') && !$lastViewedLink.hasClass('marked')) {
            $lastViewedLink.addClass('answered').css({
                'background-color': '#b55824',
                'color': '#fff',
                'border': '1px solid #b55824',
                'transform': 'perspective(200px) rotateY(30deg)',
            });
        }

        // Hide all questions and show the clicked one
        $('.mock-question').hide();
        $questionDiv.show();

        // Update the last viewed question index to the current one
        var currentQuestionIndex = $(this).parent().index();
        lastViewedQuestionIndex = currentQuestionIndex;

        $('#mock-save-next-question-btn').data('index', currentQuestionIndex);
        $('#mock-save-and-mark-question-btn').data('index', currentQuestionIndex);
    });

    // Save and Next button functionality
    $(document).on('click', '#mock-save-next-question-btn', function (e) {
        e.preventDefault();
        var currentQuestionIndex = $(this).data('index');
        var $currentQuestion = $('.mock-question').eq(currentQuestionIndex);
        var selectedOption = $currentQuestion.find('input[type="radio"]:checked');

        if (selectedOption.length > 0) {
            var $link = $('.mock-question-link').eq(currentQuestionIndex);

            $link.css({
                'background-color': 'green',
                'color': '#fff',
                'border': '1px solid green',
                'transform': 'unset',
                'border-radius': '50%',
            }).addClass('answered').removeClass('active');

            var nextQuestionIndex = currentQuestionIndex + 1;

            if (nextQuestionIndex < $('.mock-question').length) {
                $('.mock-question').hide();
                $('.mock-question').eq(nextQuestionIndex).show();
                $('#mock-save-next-question-btn').data('index', nextQuestionIndex);
                $('#mock-save-and-mark-question-btn').data('index', nextQuestionIndex);
            }
        } else {
            alert('You must select an option before proceeding.');
        }

        toggleNextPrevButtons();
    });

    // Save and Mark button functionality
    $(document).on('click', '#mock-save-and-mark-question-btn', function (e) {
        e.preventDefault();
        var currentQuestionIndex = $(this).data('index');
        var $currentQuestion = $('.mock-question').eq(currentQuestionIndex);
        var selectedOption = $currentQuestion.find('input[type="radio"]:checked');

        if (selectedOption.length > 0) {
            var $link = $('.mock-question-link').eq(currentQuestionIndex);

            $link.css({
                'background-color': '#2133a9',
                'color': '#fff',
                'border': '1px solid #2133a9',
                'transform': 'unset',
                'border-radius': '50%',
            }).addClass('reviewed').removeClass('active');

            var nextQuestionIndex = currentQuestionIndex + 1;

            if (nextQuestionIndex < $('.mock-question').length) {
                $('.mock-question').hide();
                $('.mock-question').eq(nextQuestionIndex).show();
                $('#mock-save-next-question-btn').data('index', nextQuestionIndex);
                $('#mock-save-and-mark-question-btn').data('index', nextQuestionIndex);
            }
        } else {
            alert('Choose an option before marking for review.');
        }

        toggleNextPrevButtons();
    });

    // Show summary popup
    $(document).on('click', '#mock-show-summary-btn', function (e) {
        e.preventDefault();

        var greenCount = $('.mock-question-link').filter(function () {
            return $(this).css('background-color') === 'rgb(0, 128, 0)';
        }).length;

        var violetCount = $('.mock-question-link').filter(function () {
            return $(this).css('background-color') === 'rgb(33, 51, 169)';
        }).length;

        var notAttended = $('.mock-question-link').filter(function () {
            var bgColor = $(this).css('background-color');
            return bgColor === 'rgb(181, 88, 36)';
        }).length;

        var noBackground = $('.mock-question-link').filter(function () {
            var bgColor = $(this).css('background-color');
            return bgColor === 'rgba(0, 0, 0, 0)' || bgColor === 'transparent' || bgColor === 'initial' || bgColor === 'inherit';
        }).length;

        var totalUnattended = notAttended + noBackground;

        // Set the content of the summary
        $('#mock-summary-content').html(
            '<p>Total Answered Questions: ' + greenCount + '</p>' +
            '<p>Total Marked For Reviewed: ' + violetCount + '</p>' +
            '<p>Not Attended: ' + totalUnattended + '</p>'
        );

        $('#mock-summary-popup').show();
    });

    // Close summary popup
    $(document).on('click', '#mock-close-summary-btn', function (e) {
        e.preventDefault();
        $('#mock-summary-popup').hide();
    });
});