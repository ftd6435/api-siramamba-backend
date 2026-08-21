# Siramamba Mining SA Email Template Style Guide

**Style Overview**:
A professional, corporate email design optimized for mining industry communications, featuring the brand's signature dark navy blue and golden bronze palette with clean typography and structured layouts for maximum readability across all email clients (Gmail, Outlook, Apple Mail).

## Colors
### Primary Colors
  - **primary-navy**: `#2C4A5E` - Main brand color for headers, primary buttons, and key elements
  - **primary-navy-dark**: `#1F3541` - Darker variation for hover states and emphasis
  - **primary-navy-light**: `#3D5E74` - Lighter variation for secondary elements

### Accent Colors
  - **accent-gold**: `#B8984E` - Brand accent color for "MINING SA", CTAs, and highlights
  - **accent-gold-dark**: `#9A7D3F` - Darker gold for hover states
  - **accent-gold-light**: `#C9A760` - Lighter gold for subtle accents

### Background Colors
#### Structural Backgrounds
- **bg-email**: `#FFFFFF` - Main email body background
- **bg-section**: `#F8F9FA` - Alternate section backgrounds for visual separation
- **bg-header**: `#2C4A5E` - Email header background
- **bg-footer**: `#3D5E74` - Email footer background

#### Container Backgrounds
- **bg-card**: `#FFFFFF` - Content card backgrounds
- **bg-highlight**: `#F5F2ED` - Warm neutral for highlighted sections
- **bg-info**: `#EEF3F6` - Light blue-gray for informational blocks

### Text Colors
- **color-text-primary**: `#2C3E50` - Main body text (high contrast)
- **color-text-secondary**: `#7A7A7A` - Secondary text, captions, metadata
- **color-text-tertiary**: `#95A5A6` - Tertiary text, disclaimers
- **color-text-on-navy**: `#FFFFFF` - Text on dark navy backgrounds
- **color-text-on-gold**: `#FFFFFF` - Text on golden backgrounds
- **color-text-link**: `#B8984E` - Links and clickable text

### Functional Colors
  - **color-success**: `#27AE60` - Success messages, confirmations
  - **color-error**: `#E74C3C` - Error messages, alerts
  - **color-warning**: `#F39C12` - Warnings, important notices
  - **color-info**: `#3498DB` - Informational messages

### Border Colors
  - **border-default**: `#E1E8ED` - Default borders for containers
  - **border-strong**: `#BDC3C7` - Emphasized borders
  - **border-navy**: `#2C4A5E` - Navy borders for branded elements
  - **border-gold**: `#B8984E` - Gold accent borders

## Typography
- **Font Stack**:
  - **font-family-base**: `'Helvetica Neue', Helvetica, Arial, sans-serif` — Email-safe fonts for maximum compatibility

- **Font Size & Weight** (using px for email client compatibility):
  - **Headline**: `28px font-weight: 700` - Main email headlines
  - **Subheadline**: `22px font-weight: 600` - Section headings
  - **Body Large**: `18px font-weight: 400` - Lead paragraphs, introductions
  - **Body**: `16px font-weight: 400` - Standard body text
  - **Body Small**: `14px font-weight: 400` - Secondary information
  - **Caption**: `12px font-weight: 400` - Footnotes, disclaimers, fine print
  - **Button Text**: `16px font-weight: 600` - Call-to-action buttons

- **Line Height**: 1.6 (for excellent readability in email clients)

## Border Radius
  - **Minimal**: 2px — Small elements like tags
  - **Small**: 4px — Buttons, inputs
  - **Medium**: 6px — Cards, content containers
  - **None**: 0px — Headers, footers for clean edges

## Layout & Spacing
Email-optimized spacing using pixels for consistency:
  - **Tight**: 8px - Icon-text spacing, inline elements
  - **Compact**: 12px - Button padding, small gaps
  - **Standard**: 16px - Paragraph spacing, card padding
  - **Comfortable**: 24px - Section spacing within cards
  - **Relaxed**: 32px - Major content blocks
  - **Section**: 40px - Distinct email sections

## Create Boundaries
### Borders
  - **Primary**: 1px solid #E1E8ED - Default container borders
  - **Strong**: 2px solid #2C4A5E - Emphasized elements, branded containers
  - **Accent**: 2px solid #B8984E - Highlighted sections, call-outs
  - **Subtle**: 1px solid #F8F9FA - Very light separation

### Dividers
  - **Default**: `border-top: 1px solid #E1E8ED` - Section separators
  - **Strong**: `border-top: 2px solid #BDC3C7` - Major section breaks
  - **Accent**: `border-top: 3px solid #B8984E` - Branded dividers

### Shadows & Effects
  - **Subtle**: `box-shadow: 0 1px 3px rgba(44, 74, 94, 0.08)` - Light card elevation
  - **Card**: `box-shadow: 0 2px 6px rgba(44, 74, 94, 0.12)` - Standard card shadow
  - **Elevated**: `box-shadow: 0 4px 12px rgba(44, 74, 94, 0.15)` - Important elements

## Visual Emphasis for Containers
| Technique | Implementation Notes | Best For | Avoid |
|-----------|---------------------|----------|-------|
| Background Tint | Use bg-highlight (#F5F2ED) or bg-info (#EEF3F6) | Important announcements, featured content | Overuse - maintain clean white space |
| Border Highlight | 2px solid #B8984E for left edge accent | Key information blocks, call-to-action sections | Right or bottom borders (less effective in email) |
| Gold Accent Bar | **Left edge only**: 3px solid #B8984E | Critical notices, premium content sections | Large cards, full-width sections |

## Assets
### Image
  - For logo images: Standard `<img>` with fixed dimensions for email consistency
  - For content images: `style="width: 100%; height: auto; display: block;"` for responsive behavior
  - For images with overlay: Use background-color overlay in container, not CSS filters (email client compatibility)

### Icon
- Use web-safe HTML entities or image-based icons for maximum email client support
- Icon images should be 20px-24px square with transparent backgrounds
- Example using unicode symbols:
  ```html
  <span style="font-size: 20px; color: #B8984E;">✓</span>
  ```
- For brand icons, use inline images with proper alt text

### Brand Logo:
   - Use the Siramamba Mining SA logo with pigeon illustration
   - Logo should be placed in email header
   - Recommended size: 200px width (auto height) for desktop, 150px for mobile
   - Format: PNG with transparent background for flexibility

## Email Layout - Optimized Structure

### Email Template Structure
```html
<!-- Email Container: Fixed width for desktop, 100% for mobile -->
<table role="presentation" style="width: 100%; max-width: 600px; margin: 0 auto; background-color: #FFFFFF; font-family: 'Helvetica Neue', Helvetica, Arial, sans-serif;">

  <!-- Header Section: Brand identity area -->
  <tr>
    <td style="background-color: #2C4A5E; padding: 24px 32px; text-align: center;">
      <!-- Logo and header content -->
    </td>
  </tr>

  <!-- Main Content Area: Flexible content sections -->
  <tr>
    <td style="padding: 32px 32px;">
      <!-- Email body content with proper spacing -->
      <!-- Use nested tables for complex layouts -->
    </td>
  </tr>

  <!-- Footer Section: Contact info, legal, unsubscribe -->
  <tr>
    <td style="background-color: #3D5E74; padding: 24px 32px; color: #FFFFFF; font-size: 12px; text-align: center;">
      <!-- Footer content -->
    </td>
  </tr>

</table>
```

## Email Component Examples (Inline Styles)
**Important Note**: All styles must be inline for email client compatibility. Avoid external CSS or `<style>` tags.

### Basic

- **Primary Button**:
  ```html
  <table role="presentation" cellspacing="0" cellpadding="0" border="0">
    <tr>
      <td style="border-radius: 4px; background-color: #B8984E;">
        <a href="#" style="display: inline-block; padding: 12px 32px; font-family: 'Helvetica Neue', Helvetica, Arial, sans-serif; font-size: 16px; font-weight: 600; color: #FFFFFF; text-decoration: none;">
          Call to Action
        </a>
      </td>
    </tr>
  </table>
  ```

- **Secondary Button**:
  ```html
  <table role="presentation" cellspacing="0" cellpadding="0" border="0">
    <tr>
      <td style="border-radius: 4px; background-color: #FFFFFF; border: 2px solid #2C4A5E;">
        <a href="#" style="display: inline-block; padding: 12px 32px; font-family: 'Helvetica Neue', Helvetica, Arial, sans-serif; font-size: 16px; font-weight: 600; color: #2C4A5E; text-decoration: none;">
          Secondary Action
        </a>
      </td>
    </tr>
  </table>
  ```

- **Text Link**:
  ```html
  <a href="#" style="color: #B8984E; text-decoration: none; font-weight: 600;">Learn More →</a>
  ```

### Container

- **Content Card**:
  ```html
  <table role="presentation" cellspacing="0" cellpadding="0" border="0" style="width: 100%; background-color: #FFFFFF; border: 1px solid #E1E8ED; border-radius: 6px; margin-bottom: 24px;">
    <tr>
      <td style="padding: 24px;">
        <h3 style="margin: 0 0 12px 0; font-size: 22px; font-weight: 600; color: #2C4A5E;">Card Title</h3>
        <p style="margin: 0; font-size: 16px; line-height: 1.6; color: #2C3E50;">Card content goes here with proper spacing and readability.</p>
      </td>
    </tr>
  </table>
  ```

- **Highlighted Section** (with gold accent):
  ```html
  <table role="presentation" cellspacing="0" cellpadding="0" border="0" style="width: 100%; background-color: #F5F2ED; border-left: 3px solid #B8984E; margin-bottom: 24px;">
    <tr>
      <td style="padding: 24px;">
        <p style="margin: 0; font-size: 16px; line-height: 1.6; color: #2C3E50;">Important highlighted content with gold accent bar.</p>
      </td>
    </tr>
  </table>
  ```

- **Two-Column Layout**:
  ```html
  <table role="presentation" cellspacing="0" cellpadding="0" border="0" style="width: 100%;">
    <tr>
      <td style="width: 50%; padding-right: 12px; vertical-align: top;">
        <!-- Left column content -->
      </td>
      <td style="width: 50%; padding-left: 12px; vertical-align: top;">
        <!-- Right column content -->
      </td>
    </tr>
  </table>
  ```

### Data Display

- **Information Block**:
  ```html
  <table role="presentation" cellspacing="0" cellpadding="0" border="0" style="width: 100%; background-color: #EEF3F6; border-radius: 4px; margin-bottom: 16px;">
    <tr>
      <td style="padding: 16px;">
        <p style="margin: 0 0 4px 0; font-size: 12px; font-weight: 600; color: #7A7A7A; text-transform: uppercase;">Label</p>
        <p style="margin: 0; font-size: 18px; font-weight: 700; color: #2C4A5E;">Important Value</p>
      </td>
    </tr>
  </table>
  ```

- **Divider Line**:
  ```html
  <table role="presentation" cellspacing="0" cellpadding="0" border="0" style="width: 100%;">
    <tr>
      <td style="padding: 24px 0;">
        <div style="border-top: 1px solid #E1E8ED;"></div>
      </td>
    </tr>
  </table>
  ```

## Additional Notes

### Email Client Compatibility
- **Use table-based layouts** instead of modern CSS flexbox/grid for maximum compatibility
- **Inline all CSS styles** - avoid external stylesheets and `<style>` tags where possible
- **Test across clients**: Gmail (desktop/mobile), Outlook (Windows/Mac), Apple Mail, Yahoo Mail
- **Avoid background images** in Outlook - use solid colors instead
- **Use web-safe fonts** only - avoid custom font imports

### Accessibility Guidelines
- **Minimum contrast ratio**: 4.5:1 for body text, 3:1 for large text (18px+)
- **Alt text**: Provide descriptive alt text for all images
- **Semantic HTML**: Use proper heading hierarchy (h1, h2, h3)
- **Link text**: Descriptive link text, avoid "click here"
- **Font size**: Minimum 14px for body text, 16px preferred

### Mobile Optimization
- **Max width**: 600px for email container
- **Touch targets**: Minimum 44px height for buttons
- **Font sizes**: Slightly larger on mobile (16px minimum for body)
- **Spacing**: Ensure adequate padding around clickable elements
- **Single column**: Stack content vertically on mobile

### Brand Consistency
- **Logo placement**: Always include logo in header for brand recognition
- **Color usage**: Navy and gold should dominate; use sparingly for emphasis
- **Tone**: Professional, trustworthy, industry-focused
- **Imagery**: When using photos, prefer mining industry-related imagery with professional quality

<colors_extraction>
#2C4A5E
#1F3541
#3D5E74
#B8984E
#9A7D3F
#C9A760
#FFFFFF
#F8F9FA
#F5F2ED
#EEF3F6
#2C3E50
#7A7A7A
#95A5A6
#27AE60
#E74C3C
#F39C12
#3498DB
#E1E8ED
#BDC3C7
</colors_extraction>
