<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Seat Arrangement System</title>
    <style>
        /* Previous CSS remains the same */
        body {
            font-family: Arial, sans-serif;
            margin: 20px;
            background-color: #f0f0f0;
        }
        
        .container {
            max-width: 800px;
            margin: 0 auto;
            background-color: white;
            padding: 20px;
            border-radius: 8px;
            box-shadow: 0 0 10px rgba(0,0,0,0.1);
        }
        
        .seat-grid {
            display: grid;
            grid-template-columns: repeat(8, 1fr);
            gap: 10px;
            margin: 20px 0;
        }
        
        .seat {
            width: 40px;
            height: 40px;
            border: 2px solid #ccc;
            display: flex;
            align-items: center;
            justify-content: center;
            cursor: pointer;
            transition: all 0.3s;
        }
        
        .seat.selected {
            background-color: #4CAF50;
            color: white;
            border-color: #45a049;
        }
        
        .seat.booked {
            background-color: #f44336;
            color: white;
            border-color: #da190b;
            cursor: not-allowed;
        }
        
        .form-group {
            margin-bottom: 15px;
        }
        
        .form-group label {
            display: block;
            margin-bottom: 5px;
        }
        
        .form-group input {
            width: 100%;
            padding: 8px;
            border: 1px solid #ddd;
            border-radius: 4px;
        }
        
        .btn {
            background-color: #4CAF50;
            color: white;
            padding: 10px 20px;
            border: none;
            border-radius: 4px;
            cursor: pointer;
        }
        
        .btn:hover {
            background-color: #45a049;
        }
        
        .btn:disabled {
            background-color: #cccccc;
            cursor: not-allowed;
        }
        
        .error-message {
            color: #f44336;
            margin: 10px 0;
            padding: 10px;
            border: 1px solid #f44336;
            border-radius: 4px;
            display: none;
        }
        
        .success-message {
            color: #4CAF50;
            margin: 10px 0;
            padding: 10px;
            border: 1px solid #4CAF50;
            border-radius: 4px;
            display: none;
        }
        
        .loading {
            display: none;
            margin: 10px 0;
        }
        
        .legend {
            margin-top: 20px;
            display: flex;
            gap: 20px;
        }
        
        .legend-item {
            display: flex;
            align-items: center;
            gap: 5px;
        }
        
        .legend-color {
            width: 20px;
            height: 20px;
            border: 1px solid #ccc;
        }
    </style>
</head>
<body>
    <div class="container">
        <h1>Seat Booking System</h1>
        
        <div id="errorMessage" class="error-message"></div>
        <div id="successMessage" class="success-message"></div>
        <div id="loading" class="loading">Processing your booking...</div>
        
        <form id="bookingForm" onsubmit="submitBooking(event)">
            <div class="form-group">
                <label for="name">Name:</label>
                <input type="text" id="name" required minlength="2" maxlength="100" pattern="[A-Za-z\s]+" title="Please enter a valid name (letters and spaces only)">
            </div>
            
            <div class="form-group">
                <label for="email">Email:</label>
                <input type="email" id="email" required pattern="[a-z0-9._%+-]+@[a-z0-9.-]+\.[a-z]{2,}$" title="Please enter a valid email address">
            </div>
            
            <div class="seat-grid" id="seatGrid"></div>
            
            <div class="legend">
                <div class="legend-item">
                    <div class="legend-color" style="background-color: #fff;"></div>
                    <span>Available</span>
                </div>
                <div class="legend-item">
                    <div class="legend-color" style="background-color: #4CAF50;"></div>
                    <span>Selected</span>
                </div>
                <div class="legend-item">
                    <div class="legend-color" style="background-color: #f44336;"></div>
                    <span>Booked</span>
                </div>
            </div>
            
            <button type="submit" class="btn" id="submitButton">Book Selected Seats</button>
        </form>
    </div>

    <script>
        let selectedSeats = [];
        const totalSeats = 40;
        let isProcessing = false;
        
        function showError(message) {
            const errorDiv = document.getElementById('errorMessage');
            errorDiv.textContent = message;
            errorDiv.style.display = 'block';
            setTimeout(() => {
                errorDiv.style.display = 'none';
            }, 5000);
        }
        
        function showSuccess(message) {
            const successDiv = document.getElementById('successMessage');
            successDiv.textContent = message;
            successDiv.style.display = 'block';
            setTimeout(() => {
                successDiv.style.display = 'none';
            }, 5000);
        }
        
        function setLoading(loading) {
            isProcessing = loading;
            const loadingDiv = document.getElementById('loading');
            const submitButton = document.getElementById('submitButton');
            loadingDiv.style.display = loading ? 'block' : 'none';
            submitButton.disabled = loading;
        }
        
        // Initialize seats
        function initializeSeats() {
            const seatGrid = document.getElementById('seatGrid');
            seatGrid.innerHTML = ''; // Clear existing seats
            
            for (let i = 1; i <= totalSeats; i++) {
                const seat = document.createElement('div');
                seat.className = 'seat';
                seat.innerHTML = i;
                seat.onclick = () => toggleSeat(seat, i);
                seatGrid.appendChild(seat);
            }
            loadBookedSeats();
        }
        
        // Toggle seat selection
        function toggleSeat(seatElement, seatNumber) {
            if (seatElement.classList.contains('booked') || isProcessing) {
                return;
            }
            
            if (seatElement.classList.contains('selected')) {
                seatElement.classList.remove('selected');
                selectedSeats = selectedSeats.filter(seat => seat !== seatNumber);
            } else {
                seatElement.classList.add('selected');
                selectedSeats.push(seatNumber);
            }
        }
        
        // Load booked seats from database
        function loadBookedSeats() {
            setLoading(true);
            fetch('get_booked_seats.php')
                .then(response => {
                    if (!response.ok) {
                        throw new Error('Network response was not ok');
                    }
                    return response.json();
                })
                .then(bookedSeats => {
                    if (Array.isArray(bookedSeats)) {
                        bookedSeats.forEach(seatNumber => {
                            const seatElement = document.querySelector(`.seat:nth-child(${seatNumber})`);
                            if (seatElement) {
                                seatElement.classList.add('booked');
                            }
                        });
                    }
                })
                .catch(error => {
                    console.error('Error loading booked seats:', error);
                    showError('Unable to load booked seats. Please refresh the page.');
                })
                .finally(() => {
                    setLoading(false);
                });
        }
        
        // Submit booking
        function submitBooking(event) {
            event.preventDefault();
            
            if (isProcessing) {
                return;
            }
            
            if (selectedSeats.length === 0) {
                showError('Please select at least one seat.');
                return;
            }
            
            const name = document.getElementById('name').value.trim();
            const email = document.getElementById('email').value.trim();
            
            if (!name || !email) {
                showError('Please fill in all fields.');
                return;
            }
            
            const emailRegex = /^[a-zA-Z0-9._-]+@[a-zA-Z0-9.-]+\.[a-zA-Z]{2,}$/;
            if (!emailRegex.test(email)) {
                showError('Please enter a valid email address.');
                return;
            }
            
            const nameRegex = /^[A-Za-z\s]+$/;
            if (!nameRegex.test(name)) {
                showError('Please enter a valid name (letters and spaces only).');
                return;
            }
            
            const bookingData = {
                name: name,
                email: email,
                seats: selectedSeats
            };
            
            setLoading(true);
            
            fetch('book_seats.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json'
                },
                body: JSON.stringify(bookingData)
            })
            .then(response => {
                if (!response.ok) {
                    throw new Error('Network response was not ok');
                }
                return response.json();
            })
            .then(result => {
                if (result.success) {
                    showSuccess('Booking successful!');
                    selectedSeats.forEach(seatNumber => {
                        const seatElement = document.querySelector(`.seat:nth-child(${seatNumber})`);
                        if (seatElement) {
                            seatElement.classList.remove('selected');
                            seatElement.classList.add('booked');
                        }
                    });
                    selectedSeats = [];
                    document.getElementById('bookingForm').reset();
                } else {
                    throw new Error(result.message || 'Booking failed');
                }
            })
            .catch(error => {
                console.error('Error booking seats:', error);
                showError(error.message || 'An error occurred while booking seats.');
            })
            .finally(() => {
                setLoading(false);
            });
        }
        
        // Initialize seats when page loads
        window.onload = initializeSeats;
        
        // Refresh booked seats periodically
        setInterval(loadBookedSeats, 30000); // Refresh every 30 seconds
    </script>
</body>
</html>