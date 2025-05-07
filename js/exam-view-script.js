document.addEventListener('DOMContentLoaded', function() {
    document.querySelectorAll('.exam-config-delete').forEach(function(button) {
        button.addEventListener('click', function(event) {
            event.preventDefault();
            var userInput = prompt('Type "delete" to confirm deletion:');
            if (userInput === 'delete') {
                var url = new URL(button.getAttribute('data-path'));
                url.searchParams.set('confirm_delete', 'delete');  
                window.location.href = url.toString();
            } else {
                alert('Deletion cancelled.');
            }
        });
    });
});



document.addEventListener("DOMContentLoaded", function() {
    let currentQuestionIndex = 0;
    const questions = document.querySelectorAll(".mock-question");
    const totalQuestions = questions.length;
    const prevButton = document.getElementById("mock-admin-previous-question-btn");
    const nextButton = document.getElementById("mock-admin-next-question-btn");

    function updateNavigation() {
        questions.forEach((question, index) => {
            question.style.display = index === currentQuestionIndex ? "block" : "none";
        });

       
        prevButton.disabled = currentQuestionIndex === 0;
        nextButton.disabled = currentQuestionIndex === totalQuestions - 1;
    }

    if (prevButton) {
        prevButton.addEventListener("click", function() {
            if (currentQuestionIndex > 0) {
                currentQuestionIndex--;
                updateNavigation();
            }
        });
    }

    if (nextButton) {
        nextButton.addEventListener("click", function() {
            if (currentQuestionIndex < totalQuestions - 1) {
                currentQuestionIndex++;
                updateNavigation();
            }
        });
    }

    updateNavigation(); 
});





window.addEventListener("load", () => {
    // (PART A) GET TABLE ROWS, EXCLUDE HEADER ROW
    
    var all = document.querySelectorAll("#courses_table tbody tr");
   
    // (PART B) "CURRENT ROW BEING DRAGGED"
    var dragged;
   
    // (PART C) DRAG-AND-DROP MECHANISM
    for (let tr of all) {
        // (C1) ROW IS DRAGGABLE
        tr.draggable = true;
   
        // (C2) ON DRAG START - SET "CURRENTLY DRAGGED" & DATA TRANSFER
        tr.ondragstart = e => {
            dragged = tr;
            e.dataTransfer.dropEffect = "move";
            e.dataTransfer.effectAllowed = "move";
            e.dataTransfer.setData("text/html", tr.innerHTML);
        };
   
        // (C3) PREVENT DRAG OVER - NECESSARY FOR DROP TO WORK
        tr.ondragover = e => e.preventDefault();
   
        // (C4) ON DROP - "SWAP ROWS"
        tr.ondrop = e => {
            e.preventDefault();
            if (dragged != tr) {
                // Swap innerHTML
                let draggedQid = dragged.getAttribute("data-qid");
                let targetQid = tr.getAttribute("data-qid");
                
                dragged.innerHTML = tr.innerHTML;
                tr.innerHTML = e.dataTransfer.getData("text/html");
                
                // Swap data-qid
                dragged.setAttribute("data-qid", targetQid);
                tr.setAttribute("data-qid", draggedQid);
            }
        };
   
        // (C5) COSMETICS - HIGHLIGHT ROW "ON DRAG HOVER"
        tr.ondragenter = () => tr.classList.add("hover");
        tr.ondragleave = () => tr.classList.remove("hover");
        tr.ondragend = () => {
            for (let tr of all) { tr.classList.remove("hover"); }
        };
    }

});


