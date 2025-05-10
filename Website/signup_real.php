<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Competition Management System</title>
    <link rel="stylesheet" href="./signup.css">
</head>
<body>
    <header>
		<div class="logo">Competition Management System</div>
		<nav>
			<ul>
				<li><a href="#">Home</a></li>
				<li><a href="#">Competitions</a></li>
				<li><a href="#">Contact</a></li>
				<li><a href="#">Updates</a></li>
				<li><a href="#">About Us</a></li>
				<li><a href="#">Help</a></li>
			</ul>
		</nav>
	</header>
	
    <main>
        <section class="form-section">
            <div class="form-container">
                <h1>Enter Your Details</h1><br>
                <form>
                    <label for="username">User name:</label>
                    <input type="text" id="username" name="username" required placeholder="example">
                    
                    <label for="firstname">First name:</label>
                    <input type="text" id="firstname" name="firstname" required placeholder="example">
                    
                    <label for="lastname">Last name:</label>
                    <input type="text" id="lastname" name="lastname" required placeholder="example">
                    
                    <label for="phone">Phone:</label>
                    <input type="text" id="phone" name="phone" required placeholder="+92 331 000000">
                    
                    <label for="email">Email:</label>
                    <input type="email" id="email" name="email" required placeholder="example@gmail.com">
                    
                    <label for="cnic">CNIC:</label>
                    <input type="text" id="cnic" name="cnic" required placeholder="_____-_______-_">
                    
                    <label for="password">Password:</label>
                    <input type="password" id="password" name="password" required>
                    
                    <label for="confirmpassword">Confirm password:</label>
                    <input type="password" id="confirmpassword" name="confirmpassword" required>
                    
                    <label for="role">Role:</label>
                    <select id="role" name="role">
                        <option value="select">Select</option>
                        <option value="contestant">Contestant</option>
						<option value="judge">Judge</option>
						<option value="invigilator">Invigilator</option>
                    </select>
                    
                    <div class="already-registered">
                        <a href="./index.php">Already Registered Login &rarr;</a>
                    </div>
                    
                    <button type="submit" class="submit-button">Complete Registration</button>
                </form>
            </div>
            <div class="guidelines">
                <h2>Guidelines</h2>
                <ul>
                    <li>Lorem ipsum dolor sit amet, consectetur adipiscing elit.</li>
                    <li>Sed do eiusmod tempor incididunt ut labore et dolore magna aliqua.</li>
                    <li>Ut enim ad minim veniam, quis nostrud exercitation ullamco laboris nisi ut aliquip ex ea commodo consequat.</li>
                    <li>Duis aute irure dolor in reprehenderit in voluptate velit esse cillum dolore eu fugiat nulla pariatur.</li>
                    <li>Excepteur sint occaecat cupidatat non proident, sunt in culpa qui officia deserunt mollit anim id est laborum.</li>
                </ul>
            </div>
        </section>
    </main>
</body>
</html>
