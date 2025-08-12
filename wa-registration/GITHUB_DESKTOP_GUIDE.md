# 🌐 GitHub Desktop Alternative

If you prefer a visual interface instead of command line, you can use GitHub Desktop.

## 📱 GitHub Desktop Setup

### Step 1: Download GitHub Desktop
1. Go to: https://desktop.github.com/
2. Click "Download for Windows"
3. Install and sign in with your GitHub account

### Step 2: Create Repository on GitHub.com
1. Go to https://github.com
2. Click "New Repository"
3. Name: `client-registration-system`
4. Description: "Professional Arabic client registration system"
5. Set to Public or Private
6. **Don't** check "Initialize with README"
7. Click "Create Repository"

### Step 3: Clone and Add Files
1. In GitHub Desktop, click "Clone a repository from the Internet"
2. Find your `client-registration-system` repository
3. Choose local path (like `C:\Projects\client-registration-system`)
4. Click "Clone"

### Step 4: Copy Your Files
1. Copy all files from `c:\xampp\htdocs\wa-registration\*`
2. Paste them into the cloned repository folder
3. GitHub Desktop will show all the new files

### Step 5: Commit and Push
1. In GitHub Desktop, you'll see all your files listed
2. Write commit message: "Initial commit: Client Registration System"
3. Click "Commit to main"
4. Click "Push origin"

## ✅ Done!
Your project is now on GitHub!

## 🔒 Security Note
Make sure these files are NOT copied (they should be excluded by .gitignore):
- `api/config.php` (if it exists)
- `backup/*.csv` files
- Any `.log` files
