# V4L - Form Updates

## 📝 Update register.php

Replace the form section with:

```php
<form method="POST" action="/register.php">
    <div class="form-group">
        <label for="username" class="form-label">Username *</label>
        <input
            type="text"
            id="username"
            name="username"
            class="form-input"
            required
            pattern="[a-z0-9_-]+"
            minlength="3"
            maxlength="30"
            value="<?php echo htmlspecialchars($_POST['username'] ?? ''); ?>"
            placeholder="johndoe"
            autocomplete="username"
        >
        <small class="text-muted text-small">
            3-30 characters. Lowercase letters, numbers, underscores, and hyphens only.
        </small>
    </div>

    <div class="form-group">
        <label for="full_name" class="form-label">Full Name *</label>
        <input
            type="text"
            id="full_name"
            name="full_name"
            class="form-input"
            required
            value="<?php echo htmlspecialchars($_POST['full_name'] ?? ''); ?>"
            autocomplete="name"
        >
    </div>

    <div class="form-group">
        <label for="email" class="form-label">Email Address *</label>
        <input
            type="email"
            id="email"
            name="email"
            class="form-input"
            required
            value="<?php echo htmlspecialchars($_POST['email'] ?? ''); ?>"
            autocomplete="email"
        >
        <small class="text-muted text-small">Used for password recovery</small>
    </div>

    <div class="form-group">
        <label for="phone" class="form-label">Phone Number (Optional)</label>
        <input
            type="tel"
            id="phone"
            name="phone"
            class="form-input"
            value="<?php echo htmlspecialchars($_POST['phone'] ?? ''); ?>"
            placeholder="+1-555-1234"
            autocomplete="tel"
        >
        <small class="text-muted text-small">Can also be used for password recovery</small>
    </div>

    <div class="form-group">
        <label for="password" class="form-label">Password *</label>
        <input
            type="password"
            id="password"
            name="password"
            class="form-input"
            required
            minlength="6"
            autocomplete="new-password"
        >
        <small class="text-muted text-small">Minimum 6 characters</small>
    </div>

    <div class="form-group">
        <label for="confirm_password" class="form-label">Confirm Password *</label>
        <input
            type="password"
            id="confirm_password"
            name="confirm_password"
            class="form-input"
            required
            minlength="6"
            autocomplete="new-password"
        >
    </div>

    <button type="submit" class="btn btn-primary btn-block">Create Account</button>
</form>
```

### Update register.php PHP code:

```php
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = $_POST['username'] ?? '';
    $email = $_POST['email'] ?? '';
    $password = $_POST['password'] ?? '';
    $confirmPassword = $_POST['confirm_password'] ?? '';
    $fullName = $_POST['full_name'] ?? '';
    $phone = $_POST['phone'] ?? '';

    try {
        // Validate
        if ($password !== $confirmPassword) {
            throw new Exception("Passwords do not match");
        }

        // Register
        $result = $auth->register($username, $email, $password, $fullName, $phone);
        $success = $result['message'];

    } catch (Exception $e) {
        $error = $e->getMessage();
    }
}
```

---

## 📝 Update login.php

Replace the form section with:

```php
<form method="POST" action="/login.php">
    <div class="form-group">
        <label for="identifier" class="form-label">Username, Email, or Phone</label>
        <input
            type="text"
            id="identifier"
            name="identifier"
            class="form-input"
            required
            autofocus
            value="<?php echo htmlspecialchars($_POST['identifier'] ?? ''); ?>"
            placeholder="username or email@example.com or +1-555-1234"
            autocomplete="username"
        >
        <small class="text-muted text-small">
            You can login with your username, email, or phone number
        </small>
    </div>

    <div class="form-group">
        <label for="password" class="form-label">Password</label>
        <input
            type="password"
            id="password"
            name="password"
            class="form-input"
            required
            autocomplete="current-password"
        >
    </div>

    <div class="form-group">
        <a href="/forgot-password.php" class="link text-small">Forgot your password?</a>
    </div>

    <button type="submit" class="btn btn-primary btn-block">Login</button>
</form>
```

### Update login.php PHP code:

```php
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $identifier = $_POST['identifier'] ?? '';  // Can be username, email, or phone
    $password = $_POST['password'] ?? '';

    try {
        $result = $auth->login($identifier, $password);

        // Redirect to dashboard on successful login
        header('Location: /dashboard.php');
        exit;

    } catch (Exception $e) {
        $error = $e->getMessage();
    }
}
```

---

## 📝 Update organization-form.php

Replace the form section with:

```html
<form method="POST" action="">
    <div class="form-group">
        <label for="short_name" class="form-label">Organization Short Name *</label>
        <input
            type="text"
            id="short_name"
            name="short_name"
            class="form-input"
            required
            value="<?php echo $organization ? htmlspecialchars($organization->getShortName()) : ''; ?>"
            placeholder="Acme Corp"
        >
        <small class="text-muted text-small">
            Short, recognizable name (e.g., "Tech Innovators", "Green Valley")
        </small>
    </div>

    <div class="form-group">
        <label for="legal_structure" class="form-label">Legal Structure</label>
        <select id="legal_structure" name="legal_structure" class="form-input">
            <option value="">None / Not Applicable</option>
            <optgroup label="India">
                <option value="Private Limited" <?php echo ($organization && $organization->getLegalStructure() === 'Private Limited') ? 'selected' : ''; ?>>Private Limited</option>
                <option value="Public Limited" <?php echo ($organization && $organization->getLegalStructure() === 'Public Limited') ? 'selected' : ''; ?>>Public Limited</option>
                <option value="LLP" <?php echo ($organization && $organization->getLegalStructure() === 'LLP') ? 'selected' : ''; ?>>LLP (Limited Liability Partnership)</option>
                <option value="OPC" <?php echo ($organization && $organization->getLegalStructure() === 'OPC') ? 'selected' : ''; ?>>OPC (One Person Company)</option>
            </optgroup>
            <optgroup label="USA">
                <option value="LLC" <?php echo ($organization && $organization->getLegalStructure() === 'LLC') ? 'selected' : ''; ?>>LLC (Limited Liability Company)</option>
                <option value="Inc." <?php echo ($organization && $organization->getLegalStructure() === 'Inc.') ? 'selected' : ''; ?>>Inc. (Incorporated)</option>
                <option value="Corp." <?php echo ($organization && $organization->getLegalStructure() === 'Corp.') ? 'selected' : ''; ?>>Corp. (Corporation)</option>
            </optgroup>
            <optgroup label="UK">
                <option value="Ltd." <?php echo ($organization && $organization->getLegalStructure() === 'Ltd.') ? 'selected' : ''; ?>>Ltd. (Limited)</option>
                <option value="PLC" <?php echo ($organization && $organization->getLegalStructure() === 'PLC') ? 'selected' : ''; ?>>PLC (Public Limited Company)</option>
            </optgroup>
            <optgroup label="Other">
                <option value="GmbH" <?php echo ($organization && $organization->getLegalStructure() === 'GmbH') ? 'selected' : ''; ?>>GmbH (Germany)</option>
                <option value="Proprietorship" <?php echo ($organization && $organization->getLegalStructure() === 'Proprietorship') ? 'selected' : ''; ?>>Proprietorship</option>
                <option value="Partnership" <?php echo ($organization && $organization->getLegalStructure() === 'Partnership') ? 'selected' : ''; ?>>Partnership</option>
                <option value="NGO" <?php echo ($organization && $organization->getLegalStructure() === 'NGO') ? 'selected' : ''; ?>>NGO</option>
                <option value="Trust" <?php echo ($organization && $organization->getLegalStructure() === 'Trust') ? 'selected' : ''; ?>>Trust</option>
                <option value="Foundation" <?php echo ($organization && $organization->getLegalStructure() === 'Foundation') ? 'selected' : ''; ?>>Foundation</option>
            </optgroup>
        </select>
        <small class="text-muted text-small">
            Full name will be: <strong id="full-name-preview"><?php echo $organization ? htmlspecialchars($organization->getFullName()) : 'Short Name + Legal Structure'; ?></strong>
        </small>
    </div>

    <div class="form-group">
        <label for="subdomain" class="form-label">Subdomain * <span style="color: var(--danger-color);">(Unique)</span></label>
        <div style="display: flex; align-items: center; gap: 0.5rem; margin-bottom: 0.5rem;">
            <span style="color: var(--text-light);">https://</span>
            <input
                type="text"
                id="subdomain"
                name="subdomain"
                class="form-input"
                required
                pattern="[a-z0-9-]+"
                minlength="3"
                maxlength="63"
                value="<?php echo $organization ? htmlspecialchars($organization->getSubdomain()) : ''; ?>"
                placeholder="acmecorp"
                style="flex: 1;"
                <?php echo $isEdit ? 'readonly' : ''; ?>
            >
            <span>.v4l.app</span>
        </div>
        <small class="text-muted text-small">
            ✓ 3-63 characters<br>
            ✓ Lowercase letters, numbers, and hyphens only<br>
            ✓ Must be unique across all V4L organizations<br>
            <?php if ($isEdit): ?>
                ⚠️ <strong>Subdomain cannot be changed after creation</strong>
            <?php else: ?>
                💡 Tip: Use your organization name without spaces (e.g., "techinnovators")
            <?php endif; ?>
        </small>
    </div>

    <div class="form-group">
        <label for="description" class="form-label">Description</label>
        <textarea
            id="description"
            name="description"
            class="form-input"
            rows="4"
            placeholder="Tell us about your organization..."
        ><?php echo $organization ? htmlspecialchars($organization->getDescription()) : ''; ?></textarea>
    </div>

    <div class="form-group">
        <label for="email" class="form-label">Contact Email</label>
        <input
            type="email"
            id="email"
            name="email"
            class="form-input"
            value="<?php echo $organization ? htmlspecialchars($organization->getEmail() ?? '') : ''; ?>"
            placeholder="contact@organization.com"
        >
    </div>

    <div class="form-group">
        <label for="phone" class="form-label">Contact Phone</label>
        <input
            type="tel"
            id="phone"
            name="phone"
            class="form-input"
            value="<?php echo $organization ? htmlspecialchars($organization->getPhone() ?? '') : ''; ?>"
            placeholder="+91-1234567890"
        >
    </div>

    <div class="form-group">
        <label for="address" class="form-label">Address</label>
        <textarea
            id="address"
            name="address"
            class="form-input"
            rows="3"
            placeholder="123 Main St, City, State ZIP"
        ><?php echo $organization ? htmlspecialchars($organization->getAddress() ?? '') : ''; ?></textarea>
    </div>

    <div class="form-group">
        <label for="website" class="form-label">Website</label>
        <input
            type="url"
            id="website"
            name="website"
            class="form-input"
            value="<?php echo $organization ? htmlspecialchars($organization->getWebsite() ?? '') : ''; ?>"
            placeholder="https://www.organization.com"
        >
    </div>

    <div class="form-group">
        <label style="display: flex; align-items: center; gap: 0.5rem; cursor: pointer;">
            <input
                type="checkbox"
                name="is_active"
                <?php echo (!$organization || $organization->getIsActive()) ? 'checked' : ''; ?>
            >
            <span>Active</span>
        </label>
        <small class="text-muted text-small">Inactive organizations are hidden but not deleted</small>
    </div>

    <div class="form-group" style="display: flex; gap: 1rem;">
        <button type="submit" class="btn btn-primary">
            <?php echo $isEdit ? 'Update Organization' : 'Create Organization'; ?>
        </button>
        <a href="/organizations.php" class="btn btn-secondary">Cancel</a>
    </div>
</form>

<script>
// Auto-generate subdomain from short name
document.getElementById('short_name')?.addEventListener('input', function(e) {
    const subdomainInput = document.getElementById('subdomain');
    if (!subdomainInput.readOnly && !subdomainInput.value) {
        const suggested = e.target.value
            .toLowerCase()
            .replace(/[^a-z0-9]+/g, '-')
            .replace(/^-+|-+$/g, '');
        subdomainInput.value = suggested;
    }
    updateFullNamePreview();
});

document.getElementById('legal_structure')?.addEventListener('change', updateFullNamePreview);

function updateFullNamePreview() {
    const shortName = document.getElementById('short_name')?.value || 'Short Name';
    const legalStructure = document.getElementById('legal_structure')?.value;
    const preview = document.getElementById('full-name-preview');

    if (preview) {
        preview.textContent = legalStructure ? `${shortName} ${legalStructure}` : shortName;
    }
}
</script>
```

### Update organization-form.php PHP code:

```php
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    try {
        if (!$organization) {
            $organization = new Organization();
        }

        $organization->setShortName($_POST['short_name'] ?? '');
        $organization->setLegalStructure($_POST['legal_structure'] ?? '');
        $organization->setSubdomain($_POST['subdomain'] ?? '');
        $organization->setDescription($_POST['description'] ?? '');
        $organization->setEmail($_POST['email'] ?? '');
        $organization->setPhone($_POST['phone'] ?? '');
        $organization->setAddress($_POST['address'] ?? '');
        $organization->setWebsite($_POST['website'] ?? '');
        $organization->setIsActive(isset($_POST['is_active']) ? 1 : 0);

        // Validate
        $errors = $organization->validate();
        if (!empty($errors)) {
            throw new Exception(implode(', ', $errors));
        }

        if ($isEdit) {
            $orgRepo->update($organization, $user->getId());
            $_SESSION['success_message'] = 'Organization updated successfully!';
        } else {
            $orgRepo->create($organization, $user->getId());
            $_SESSION['success_message'] = 'Organization created successfully!';
        }

        header('Location: /organizations.php');
        exit;

    } catch (Exception $e) {
        $error = $e->getMessage();
    }
}
```

---

## 📝 Update organizations.php (List Page)

Update the table row display:

```php
<tr style="border-bottom: 1px solid var(--border-color);">
    <td style="padding: 1rem;">
        <strong><?php echo htmlspecialchars($org->getFullName()); ?></strong>
        <?php if ($org->getUrl()): ?>
            <br>
            <small class="text-muted">
                <a href="<?php echo $org->getUrl(); ?>" target="_blank" class="link" style="color: var(--primary-color);">
                    <?php echo $org->getSubdomain(); ?>.v4l.app ↗
                </a>
            </small>
        <?php endif; ?>
        <?php if ($org->getDescription()): ?>
            <br><small class="text-muted"><?php echo htmlspecialchars(substr($org->getDescription(), 0, 60)); ?><?php echo strlen($org->getDescription()) > 60 ? '...' : ''; ?></small>
        <?php endif; ?>
    </td>
    <td style="padding: 1rem;">
        <?php echo $org->getEmail() ? htmlspecialchars($org->getEmail()) : '-'; ?>
    </td>
    <td style="padding: 1rem;">
        <?php echo $org->getPhone() ? htmlspecialchars($org->getPhone()) : '-'; ?>
    </td>
    <td style="padding: 1rem;">
        <?php if ($org->getIsActive()): ?>
            <span style="color: var(--secondary-color);">✓ Active</span>
        <?php else: ?>
            <span style="color: var(--text-light);">○ Inactive</span>
        <?php endif; ?>
    </td>
    <td style="padding: 1rem; white-space: nowrap;">
        <?php echo date('M j, Y', strtotime($org->getCreatedAt())); ?>
    </td>
    <td style="padding: 1rem; white-space: nowrap;">
        <a href="/organization-form.php?id=<?php echo $org->getId(); ?>" class="btn btn-secondary" style="padding: 0.5rem 1rem; margin-right: 0.5rem;">Edit</a>
        <a href="/organization-delete.php?id=<?php echo $org->getId(); ?>" class="btn btn-danger" style="padding: 0.5rem 1rem;" onclick="return confirm('Move this organization to trash?');">Delete</a>
    </td>
</tr>
```

---

## 🎯 Quick Testing Guide

### Test Username Registration:
1. Go to `/register`
2. Fill username: "johndoe"
3. Fill email, password, etc.
4. Submit - should succeed

### Test Username Login:
1. Go to `/login`
2. Enter username: "johndoe"
3. Enter password
4. Should login successfully

### Test Organization Creation:
1. Go to `/organizations`
2. Click "New Organization"
3. Short Name: "Tech Innovators"
4. Legal Structure: "Private Limited"
5. Subdomain: "techinnovators"
6. Submit
7. Should show "Tech Innovators Private Limited" with link to techinnovators.v4l.app

---

**All form updates complete!**
