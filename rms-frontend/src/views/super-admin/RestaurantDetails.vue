<template>
  <section class="super-admin-restaurant-details page-shell">
    <!-- Header -->
    <header class="page-header">
      <div class="page-header-main">
        <router-link
          :to="{ name: 'super-admin-restaurants' }"
          class="back-link"
        >
          <i class="bi bi-arrow-left"></i>
          <span>Back to Restaurants</span>
        </router-link>

        <div class="page-title-row">
          <div class="title-icon">
            <i class="bi bi-shop"></i>
          </div>
          <div>
            <h1>Restaurant Details</h1>
            <p v-if="restaurant" class="page-subtitle">
              {{ restaurant.name }}
            </p>
          </div>
        </div>
      </div>

      <div class="page-actions">
        <a
          v-if="canOpenRestaurant"
          :href="getTenantUrl()"
          class="btn btn-outline-success"
          target="_blank"
          rel="noopener noreferrer"
        >
          <i class="bi bi-box-arrow-up-right me-1"></i>
          Open Restaurant
        </a>

        <button
          type="button"
          class="btn btn-primary"
          @click="loadAll"
          :disabled="loading || paymentSubmitting || renewalSubmitting || upgradeSubmitting || planUpdateSubmitting"
        >
          <span
            v-if="loading"
            class="spinner-border spinner-border-sm me-1"
            aria-hidden="true"
          ></span>
          <i v-else class="bi bi-arrow-clockwise me-1"></i>
          {{ loading ? 'Refreshing...' : 'Refresh' }}
        </button>
      </div>
    </header>

    <!-- Loading -->
    <div v-if="loading" class="state-card info-state">
      <div class="state-icon">
        <span class="spinner-border" aria-hidden="true"></span>
      </div>
      <div>
        <strong>Loading restaurant details</strong>
        <p>Please wait while the latest information is loaded.</p>
      </div>
    </div>

    <!-- Error -->
    <div v-if="error" class="alert alert-danger page-alert" role="alert">
      <i class="bi bi-exclamation-triangle-fill me-2"></i>
      {{ error }}
    </div>

    <div v-if="stripeReturnMessage" class="alert alert-success page-alert" role="status">
      <i class="bi bi-check-circle-fill me-2"></i>{{ stripeReturnMessage }}
    </div>
    <div v-if="stripeReturnError" class="alert alert-warning page-alert" role="status">
      <i class="bi bi-exclamation-triangle-fill me-2"></i>{{ stripeReturnError }}
    </div>

    <template v-if="!loading && restaurant">
      <!-- Restaurant Overview -->
      <section class="content-card overview-card">
        <div class="content-card-header">
          <div class="section-heading">
            <div class="section-icon">
              <i class="bi bi-building"></i>
            </div>
            <div>
              <h2>Restaurant Information</h2>
              <p>Basic information and current platform status.</p>
            </div>
          </div>

          <span
            class="status-badge"
            :class="{
              'status-success': restaurant.status === 'active',
              'status-warning': restaurant.status === 'pending',
              'status-danger': restaurant.status === 'failed',
              'status-muted': !['active', 'pending', 'failed'].includes(restaurant.status)
            }"
          >
            <span class="status-dot"></span>
            {{ restaurant.status }}
          </span>
        </div>

        <div class="content-card-body">
          <div class="info-grid info-grid-3">
            <div class="info-item">
              <span class="info-label">Restaurant Name</span>
              <span class="info-value strong">{{ restaurant.name || '—' }}</span>
            </div>

            <div class="info-item">
              <span class="info-label">Slug</span>
              <span class="info-value mono">{{ restaurant.slug || '—' }}</span>
            </div>

            <div class="info-item">
              <span class="info-label">Tenant Key</span>
              <span class="info-value mono">{{ restaurant.tenant_key || '—' }}</span>
            </div>

            <div class="info-item">
              <span class="info-label">Owner</span>
              <span class="info-value">{{ restaurant.owner_name || '—' }}</span>
            </div>

            <div class="info-item">
              <span class="info-label">Owner Email</span>
              <span class="info-value break-word">{{ restaurant.owner_email || '—' }}</span>
            </div>

            <div class="info-item">
              <span class="info-label">Phone</span>
              <span class="info-value">{{ restaurant.phone || '—' }}</span>
            </div>

            <div class="info-item">
              <span class="info-label">City</span>
              <span class="info-value">{{ restaurant.city || '—' }}</span>
            </div>

            <div class="info-item">
              <span class="info-label">Country</span>
              <span class="info-value">{{ restaurant.country || '—' }}</span>
            </div>

            <div class="info-item info-item-wide">
              <span class="info-label">Address</span>
              <span class="info-value">{{ restaurant.address || '—' }}</span>
            </div>
          </div>
        </div>
      </section>

      <!-- Tenant Admin Access -->
      <section class="content-card">
        <div class="content-card-header">
          <div class="section-heading">
            <div class="section-icon">
              <i class="bi bi-person-lock"></i>
            </div>
            <div>
              <h2>Tenant Admin Access</h2>
              <p>Manage the administrator credentials for this restaurant.</p>
            </div>
          </div>

          <button
            v-if="restaurant.status === 'active'"
            type="button"
            class="btn btn-outline-primary"
            :disabled="credentialsLoading"
            @click="handleResetAdminCredentials"
          >
            <span
              v-if="credentialsLoading"
              class="spinner-border spinner-border-sm me-1"
            ></span>
            <i v-else class="bi bi-key me-1"></i>
            {{ credentialsLoading ? 'Generating...' : 'Generate New Password' }}
          </button>
        </div>

        <div class="content-card-body">
          <div
            v-if="credentialsMessage"
            class="alert alert-success soft-alert"
            role="alert"
          >
            <i class="bi bi-check-circle-fill me-2"></i>
            {{ credentialsMessage }}
          </div>

          <div
            v-if="credentialsError"
            class="alert alert-danger soft-alert"
            role="alert"
          >
            <i class="bi bi-exclamation-triangle-fill me-2"></i>
            {{ credentialsError }}
          </div>

          <div v-if="adminCredentials" class="credentials-grid">
            <div class="credential-box">
              <span class="info-label">Username</span>
              <div class="input-group">
                <input
                  :value="adminCredentials.username"
                  type="text"
                  class="form-control"
                  readonly
                />
                <button
                  type="button"
                  class="btn btn-outline-secondary"
                  title="Copy username"
                  @click="copyCredential(adminCredentials.username)"
                >
                  <i class="bi bi-copy"></i>
                </button>
              </div>
            </div>

            <div class="credential-box credential-box-password">
              <span class="info-label">Temporary Password</span>
              <div class="input-group">
                <input
                  :value="adminCredentials.password"
                  type="text"
                  class="form-control"
                  readonly
                />
                <button
                  type="button"
                  class="btn btn-outline-secondary"
                  title="Copy password"
                  @click="copyCredential(adminCredentials.password)"
                >
                  <i class="bi bi-copy"></i>
                </button>
              </div>
            </div>

            <div class="security-note">
              <div class="security-note-icon">
                <i class="bi bi-shield-lock"></i>
              </div>
              <div>
                <strong>Save it securely</strong>
                <p>This temporary password will not be retrievable later.</p>
              </div>
            </div>
          </div>

          <div v-else class="empty-inline">
            <i class="bi bi-key"></i>
            <span>
              No temporary admin password is currently displayed.
              Generate a new password when needed.
            </span>
          </div>
        </div>
      </section>

      <!-- Tenant Database -->
      <section class="content-card">
        <div class="content-card-header">
          <div class="section-heading">
            <div class="section-icon">
              <i class="bi bi-database"></i>
            </div>
            <div>
              <h2>Tenant Database</h2>
              <p>Provisioning and database connection information.</p>
            </div>
          </div>
        </div>

        <div class="content-card-body">
          <template v-if="tenantDatabase">
            <div class="info-grid info-grid-3">
              <div class="info-item info-item-wide">
                <span class="info-label">Database Name</span>
                <span class="info-value strong mono">{{ tenantDatabase.database_name || '—' }}</span>
              </div>

              <div class="info-item">
                <span class="info-label">Host</span>
                <span class="info-value">{{ tenantDatabase.database_host || '—' }}</span>
              </div>

              <div class="info-item">
                <span class="info-label">Port</span>
                <span class="info-value">{{ tenantDatabase.database_port || '—' }}</span>
              </div>

              <div class="info-item">
                <span class="info-label">Connection</span>
                <span class="info-value mono">{{ tenantDatabase.connection_name || '—' }}</span>
              </div>

              <div class="info-item">
                <span class="info-label">Status</span>
                <span
                  class="status-badge"
                  :class="{
                    'status-success': tenantDatabase.status === 'active',
                    'status-danger': tenantDatabase.status === 'failed',
                    'status-warning': !['active', 'failed'].includes(tenantDatabase.status)
                  }"
                >
                  <span class="status-dot"></span>
                  {{ tenantDatabase.status || 'unknown' }}
                </span>
              </div>

              <div class="info-item">
                <span class="info-label">Migrated At</span>
                <span class="info-value">{{ formatDateTime(tenantDatabase.migrated_at) }}</span>
              </div>

              <div class="info-item">
                <span class="info-label">Provisioned At</span>
                <span class="info-value">{{ formatDateTime(tenantDatabase.provisioned_at) }}</span>
              </div>
            </div>
          </template>

          <div v-else class="empty-state">
            <div class="empty-state-icon">
              <i class="bi bi-database-slash"></i>
            </div>
            <div>
              <strong>Tenant database not provisioned</strong>
              <p>No tenant database information is currently available.</p>
            </div>
          </div>
        </div>
      </section>

      <!-- Client Access -->
      <section class="content-card client-access-card">
        <div class="content-card-header">
          <div class="section-heading">
            <div class="section-icon">
              <i class="bi bi-link-45deg"></i>
            </div>
            <div>
              <h2>Client Access</h2>
              <p>
                Share this URL with the restaurant owner after the system is live.
              </p>
            </div>
          </div>

          <span
            class="client-environment-badge"
            :class="isLocalEnvironment ? 'is-development' : 'is-production'"
          >
            <span class="status-dot"></span>
            {{ isLocalEnvironment ? 'Development' : 'Production' }}
          </span>
        </div>

        <div class="content-card-body">
          <div
            v-if="restaurant.tenant_key"
            class="client-access-panel"
          >
            <div class="client-access-main">
              <span class="info-label">Restaurant Access URL</span>

              <div class="client-url-row">
                <div class="client-url-value mono">
                  {{ getTenantUrl() }}
                </div>

                <button
                  type="button"
                  class="btn btn-outline-primary client-copy-button"
                  @click="copyClientAccessUrl"
                >
                  <i
                    :class="
                      clientAccessCopied
                        ? 'bi bi-check2'
                        : 'bi bi-copy'
                    "
                  ></i>

                  {{ clientAccessCopied ? 'Copied' : 'Copy URL' }}
                </button>
              </div>

              <div
                v-if="isLocalEnvironment"
                class="client-access-note development-note"
              >
                <i class="bi bi-info-circle"></i>

                <span>
                  This localhost URL is for development only.
                  After deployment, the same tenant path will automatically use
                  your live RMS domain.
                </span>
              </div>

              <div
                v-else
                class="client-access-note production-note"
              >
                <i class="bi bi-check-circle"></i>

                <span>
                  This is the client-facing production URL you can share with
                  the restaurant owner.
                </span>
              </div>
            </div>

            <div class="client-access-grid">
              <div class="client-access-info-box">
                <span class="info-label">Tenant Key</span>

                <div class="client-key-row">
                  <strong class="mono">
                    {{ restaurant.tenant_key }}
                  </strong>

                  <button
                    type="button"
                    class="client-icon-copy"
                    :title="tenantKeyCopied ? 'Copied' : 'Copy tenant key'"
                    @click="copyTenantKey"
                  >
                    <i
                      :class="
                        tenantKeyCopied
                          ? 'bi bi-check2'
                          : 'bi bi-copy'
                      "
                    ></i>
                  </button>
                </div>
              </div>

              <div class="client-access-info-box">
                <span class="info-label">Access Status</span>

                <span
                  class="status-badge"
                  :class="canOpenRestaurant ? 'status-success' : 'status-warning'"
                >
                  <span class="status-dot"></span>
                  {{ canOpenRestaurant ? 'Available' : 'Currently Unavailable' }}
                </span>
              </div>
            </div>

            <div class="client-access-actions">
              <a
                v-if="canOpenRestaurant"
                :href="getTenantUrl()"
                class="btn btn-success"
                target="_blank"
                rel="noopener noreferrer"
              >
                <i class="bi bi-box-arrow-up-right me-1"></i>
                Open Restaurant
              </a>

              <button
                type="button"
                class="btn btn-outline-secondary"
                @click="copyClientAccessUrl"
              >
                <i class="bi bi-send me-1"></i>
                {{ clientAccessCopied ? 'URL Copied' : 'Copy for Client' }}
              </button>
            </div>
          </div>

          <div
            v-else
            class="empty-state"
          >
            <div class="empty-state-icon">
              <i class="bi bi-link-45deg"></i>
            </div>

            <div>
              <strong>Tenant access is not available</strong>
              <p>This restaurant does not currently have a tenant key.</p>
            </div>
          </div>
        </div>
      </section>

      <!-- Client Handover -->
      <section class="content-card client-handover-card">
        <div class="content-card-header">
          <div class="section-heading">
            <div class="section-icon">
              <i class="bi bi-send-check"></i>
            </div>

            <div>
              <h2>Client Handover</h2>
              <p>
                Prepare the restaurant access details in one place
                before sending them to the client.
              </p>
            </div>
          </div>

          <span
            class="handover-status-badge"
            :class="hasHandoverCredentials ? 'is-ready' : 'is-pending'"
          >
            <span class="status-dot"></span>
            {{ hasHandoverCredentials ? 'Ready to Share' : 'Credentials Required' }}
          </span>
        </div>

        <div class="content-card-body">
          <div
            v-if="handoverMessage"
            class="alert alert-success soft-alert"
            role="alert"
          >
            <i class="bi bi-check-circle-fill me-2"></i>
            {{ handoverMessage }}
          </div>

          <div
            v-if="handoverError"
            class="alert alert-danger soft-alert"
            role="alert"
          >
            <i class="bi bi-exclamation-triangle-fill me-2"></i>
            {{ handoverError }}
          </div>

          <div class="handover-grid">
            <!-- Access URL -->
            <article class="handover-detail-card">
              <div class="handover-detail-head">
                <div>
                  <span class="info-label">Login / Access URL</span>
                  <strong>Restaurant Portal</strong>
                </div>

                <span class="handover-detail-icon">
                  <i class="bi bi-link-45deg"></i>
                </span>
              </div>

              <div class="handover-value-row">
                <span class="handover-value mono">
                  {{ getTenantUrl() }}
                </span>

                <button
                  type="button"
                  class="handover-copy-icon"
                  title="Copy access URL"
                  @click="copyClientAccessUrl"
                >
                  <i
                    :class="
                      clientAccessCopied
                        ? 'bi bi-check2'
                        : 'bi bi-copy'
                    "
                  ></i>
                </button>
              </div>
            </article>

            <!-- Username -->
            <article class="handover-detail-card">
              <div class="handover-detail-head">
                <div>
                  <span class="info-label">Admin Username</span>
                  <strong>Tenant Administrator</strong>
                </div>

                <span class="handover-detail-icon">
                  <i class="bi bi-person"></i>
                </span>
              </div>

              <div
                v-if="adminCredentials?.username"
                class="handover-value-row"
              >
                <span class="handover-value mono">
                  {{ adminCredentials.username }}
                </span>

                <button
                  type="button"
                  class="handover-copy-icon"
                  title="Copy username"
                  @click="copyCredential(adminCredentials.username)"
                >
                  <i class="bi bi-copy"></i>
                </button>
              </div>

              <div
                v-else
                class="handover-missing-value"
              >
                Generate credentials to display the username.
              </div>
            </article>

            <!-- Password -->
            <article class="handover-detail-card">
              <div class="handover-detail-head">
                <div>
                  <span class="info-label">Temporary Password</span>
                  <strong>One-time Handover Credential</strong>
                </div>

                <span class="handover-detail-icon">
                  <i class="bi bi-key"></i>
                </span>
              </div>

              <div
                v-if="adminCredentials?.password"
                class="handover-value-row"
              >
                <span class="handover-value mono">
                  {{ adminCredentials.password }}
                </span>

                <button
                  type="button"
                  class="handover-copy-icon"
                  title="Copy temporary password"
                  @click="copyCredential(adminCredentials.password)"
                >
                  <i class="bi bi-copy"></i>
                </button>
              </div>

              <div
                v-else
                class="handover-missing-value"
              >
                Temporary password is only shown after generating new credentials.
              </div>
            </article>
          </div>

          <div class="handover-security-note">
            <div class="handover-security-icon">
              <i class="bi bi-shield-lock"></i>
            </div>

            <div>
              <strong>Credential safety</strong>

              <p>
                Generating new credentials resets the current tenant admin password.
                Only generate them when you are ready to hand over or reissue access.
              </p>
            </div>
          </div>

          <div class="handover-actions">
            <button
              v-if="restaurant.status === 'active'"
              type="button"
              class="btn btn-outline-primary"
              :disabled="credentialsLoading"
              @click="handleResetAdminCredentials"
            >
              <span
                v-if="credentialsLoading"
                class="spinner-border spinner-border-sm me-1"
              ></span>

              <i
                v-else
                class="bi bi-key me-1"
              ></i>

              {{
                credentialsLoading
                  ? 'Generating...'
                  : hasHandoverCredentials
                    ? 'Generate New Credentials'
                    : 'Generate Handover Credentials'
              }}
            </button>

            <button
              type="button"
              class="btn btn-primary"
              :disabled="!hasHandoverCredentials"
              @click="copyFullHandover"
            >
              <i
                :class="
                  handoverCopied
                    ? 'bi bi-check2 me-1'
                    : 'bi bi-copy me-1'
                "
              ></i>

              {{
                handoverCopied
                  ? 'Handover Copied'
                  : 'Copy Full Handover'
              }}
            </button>

            <button
              v-if="canShareHandover"
              type="button"
              class="btn btn-success"
              :disabled="!hasHandoverCredentials || handoverSharing"
              @click="shareHandover"
            >
              <span
                v-if="handoverSharing"
                class="spinner-border spinner-border-sm me-1"
              ></span>

              <i
                v-else
                class="bi bi-share me-1"
              ></i>

              {{ handoverSharing ? 'Sharing...' : 'Share' }}
            </button>
          </div>

          <div
            v-if="hasHandoverCredentials"
            class="handover-preview"
          >
            <div class="handover-preview-head">
              <span class="info-label">Message Preview</span>
              <span>Ready to copy or share</span>
            </div>

            <pre>{{ handoverText }}</pre>
          </div>
        </div>
      </section>

      <!-- Technical / Provisioned Domain -->
      <section class="content-card technical-domain-card">
        <div class="content-card-header">
          <div class="section-heading">
            <div class="section-icon">
              <i class="bi bi-globe2"></i>
            </div>

            <div>
              <h2>Technical Domain</h2>
              <p>
                Provisioned domain metadata. This is separate from the client access URL.
              </p>
            </div>
          </div>

          <span
            v-if="primaryDomain"
            class="technical-domain-type"
          >
            {{ primaryDomain.type || 'domain' }}
          </span>
        </div>

        <div class="content-card-body">
          <template v-if="primaryDomain">
            <div class="technical-domain-highlight">
              <div class="domain-icon">
                <i class="bi bi-globe"></i>
              </div>

              <div class="domain-main">
                <span class="info-label">
                  {{
                    isLocalTechnicalDomain
                      ? 'Development Domain Record'
                      : 'Provisioned Domain Record'
                  }}
                </span>

                <strong class="mono">
                  {{ primaryDomain.domain || '—' }}
                </strong>
              </div>

              <span
                v-if="isLocalTechnicalDomain"
                class="technical-only-badge"
              >
                Internal / Development
              </span>
            </div>

            <div
              v-if="isLocalTechnicalDomain"
              class="technical-domain-warning"
            >
              <i class="bi bi-info-circle-fill"></i>

              <span>
                Do not give this <strong>.local</strong> address to the client.
                Use the Client Access URL above instead.
              </span>
            </div>

            <div class="info-grid info-grid-3 mt-4">
              <div class="info-item">
                <span class="info-label">Type</span>
                <span class="info-value">
                  {{ primaryDomain.type || '—' }}
                </span>
              </div>

              <div class="info-item">
                <span class="info-label">SSL</span>
                <span class="info-value">
                  {{ primaryDomain.ssl_status || '—' }}
                </span>
              </div>

              <div class="info-item">
                <span class="info-label">Verification</span>

                <span
                  class="status-badge"
                  :class="primaryDomain.is_verified ? 'status-success' : 'status-warning'"
                >
                  <span class="status-dot"></span>

                  {{
                    primaryDomain.is_verified
                      ? 'Verified'
                      : 'Not Verified'
                  }}
                </span>
              </div>
            </div>
          </template>

          <div
            v-else
            class="empty-state"
          >
            <div class="empty-state-icon">
              <i class="bi bi-globe2"></i>
            </div>

            <div>
              <strong>No technical domain record</strong>
              <p>
                Client access can still work through the tenant-key URL.
              </p>
            </div>
          </div>
        </div>
      </section>

      <!-- Subscription -->
      <section v-if="subscription" class="content-card">
        <div class="content-card-header subscription-header">
          <div class="section-heading">
            <div class="section-icon">
              <i class="bi bi-calendar-check"></i>
            </div>
            <div>
              <h2>Subscription</h2>
              <p>Plan details, validity and lifecycle controls.</p>
            </div>
          </div>

          <div class="subscription-header-actions">
            <span
              class="status-badge"
              :class="{
                'status-success': subscription.status === 'active',
                'status-warning': subscription.status === 'pending',
                'status-danger': subscription.status === 'cancelled',
                'status-muted': subscription.status === 'expired'
              }"
            >
              <span class="status-dot"></span>
              {{ subscription.status }}
            </span>

            <!-- Active Trial -->
            <button
              v-if="isTrialSubscription"
              type="button"
              class="btn btn-success"
              @click="openUpgradePayment"
              :disabled="upgradeSubmitting"
            >
              <i class="bi bi-lightning-charge me-1"></i>
              Update Plan
            </button>

            <!-- Expired Trial -->
            <button
              v-else-if="isExpiredTrialSubscription"
              type="button"
              class="btn btn-warning"
              @click="openUpgradePayment"
              :disabled="upgradeSubmitting"
            >
              <i class="bi bi-arrow-clockwise me-1"></i>
              Activate Paid Plan
            </button>

            <!-- Active Paid -->
            <button
              v-else-if="isPaidSubscription"
              type="button"
              class="btn btn-primary"
              @click="openPlanUpdate"
              :disabled="planUpdateSubmitting"
            >
              <i class="bi bi-arrow-repeat me-1"></i>
              Update Plan
            </button>

            <!-- Expired / Cancelled Paid -->
            <button
              v-else-if="
                ['expired', 'cancelled'].includes(subscription.status) &&
                Number(subscription.plan?.price || 0) > 0
              "
              type="button"
              class="btn btn-warning"
              @click="openRenewalPayment"
              :disabled="renewalSubmitting"
            >
              <i class="bi bi-arrow-clockwise me-1"></i>
              Renew Subscription
            </button>
          </div>
        </div>

        <div class="content-card-body">
          <!-- Plan summary -->
          <div class="subscription-summary">
            <div class="plan-summary-main">
              <span class="info-label">Current Plan</span>
              <h3>{{ subscription.plan?.name || '—' }}</h3>
              <p v-if="subscription.plan?.description">
                {{ subscription.plan.description }}
              </p>
            </div>

            <div class="plan-price">
              <span class="info-label">Price</span>
              <strong>
                {{ subscription.plan?.currency || 'BDT' }}
                {{ formatPrice(subscription.plan?.price) }}
              </strong>
              <span>
                / {{ billingLabel(subscription.plan) }}
              </span>
            </div>
          </div>

          <!-- Subscription period -->
          <div class="subsection-divider">
            <div class="subsection-title">
              <i class="bi bi-clock-history"></i>
              Subscription Period
            </div>
          </div>

          <div class="info-grid info-grid-3">
            <div class="info-item">
              <span class="info-label">Start Date</span>
              <span class="info-value">
                {{ formatDateTime(subscription.start_at) }}
              </span>
            </div>

            <div class="info-item">
              <span class="info-label">End Date</span>
              <span class="info-value">
                {{ formatDateTime(subscription.end_at) }}
              </span>
            </div>

            <div
              v-if="subscription.end_at && subscription.status === 'active'"
              class="info-item"
            >
              <span class="info-label">Remaining</span>
              <span class="info-value strong">
                {{ remainingSubscriptionText(subscription.end_at) }}
              </span>
            </div>
          </div>

          <!-- Features -->
          <div class="subsection-divider">
            <div class="subsection-title">
              <i class="bi bi-stars"></i>
              Plan Features
            </div>
          </div>

          <div v-if="subscription.plan?.features?.length" class="feature-list">
            <span
              v-for="feature in subscription.plan.features"
              :key="feature"
              class="feature-pill"
            >
              <i class="bi bi-check-circle-fill"></i>
              {{ feature }}
            </span>
          </div>
          <div v-else class="empty-inline">
            <i class="bi bi-info-circle"></i>
            <span>No feature information is available for this plan.</span>
          </div>

          <!-- Subscription Status Management -->
          <div class="subsection-divider">
            <div class="subsection-title">
              <i class="bi bi-sliders"></i>
              Subscription Status Management
            </div>
          </div>

          <div
            v-if="subscriptionMessage"
            class="alert alert-success soft-alert"
            role="alert"
          >
            <i class="bi bi-check-circle-fill me-2"></i>
            {{ subscriptionMessage }}
          </div>

          <div
            v-if="subscriptionError"
            class="alert alert-danger soft-alert"
            role="alert"
          >
            <i class="bi bi-exclamation-triangle-fill me-2"></i>
            {{ subscriptionError }}
          </div>

          <div class="status-management-grid">
            <div>
              <label for="subscription-status" class="form-label">
                Change Status
              </label>
              <select
                id="subscription-status"
                v-model="selectedSubscriptionStatus"
                class="form-select"
                :disabled="subscriptionUpdating"
              >
                <option value="" disabled>Select status</option>
                <option value="active">Active</option>
                <option value="cancelled">Cancelled</option>
                <option value="expired">Expired</option>
              </select>
            </div>

            <div>
              <label for="subscription-cancel-reason" class="form-label">
                Cancellation Reason
                <span
                  v-if="selectedSubscriptionStatus === 'cancelled'"
                  class="text-danger"
                >*</span>
              </label>
              <input
                id="subscription-cancel-reason"
                v-model="subscriptionCancelReason"
                type="text"
                class="form-control"
                placeholder="Reason for cancellation"
                :disabled="subscriptionUpdating"
              />
            </div>

            <div class="status-management-action">
              <button
                type="button"
                class="btn btn-primary w-100"
                :disabled="
                  subscriptionUpdating ||
                  !selectedSubscriptionStatus ||
                  selectedSubscriptionStatus === subscription.status
                "
                @click="handleSubscriptionStatusUpdate"
              >
                <span
                  v-if="subscriptionUpdating"
                  class="spinner-border spinner-border-sm me-1"
                ></span>
                <i v-else class="bi bi-arrow-repeat me-1"></i>
                {{ subscriptionUpdating ? 'Updating...' : 'Update Subscription' }}
              </button>
            </div>
          </div>
        </div>
      </section>

      <!-- No subscription -->
      <section v-else class="content-card">
        <div class="content-card-body">
          <div class="empty-state">
            <div class="empty-state-icon">
              <i class="bi bi-calendar-x"></i>
            </div>
            <div>
              <strong>No subscription found</strong>
              <p>This restaurant currently does not have a subscription record.</p>
            </div>
          </div>
        </div>
      </section>

      <!-- Initial Payment -->
      <section
        v-if="restaurant.status === 'pending' && subscription"
        class="content-card payment-card"
      >
        <div class="content-card-header payment-header">
          <div class="section-heading">
            <div class="section-icon payment-icon">
              <i class="bi bi-credit-card"></i>
            </div>
            <div>
              <h2>Complete Payment</h2>
              <p>Complete the initial payment to activate this restaurant.</p>
            </div>
          </div>

          <span class="status-badge status-warning">
            <span class="status-dot"></span>
            Payment Required
          </span>
        </div>

        <div class="content-card-body">
          <div
            v-if="paymentMessage"
            class="alert alert-success soft-alert"
            role="alert"
          >
            <i class="bi bi-check-circle-fill me-2"></i>
            {{ paymentMessage }}
          </div>

          <div
            v-if="paymentError"
            class="alert alert-danger soft-alert"
            role="alert"
          >
            <i class="bi bi-exclamation-triangle-fill me-2"></i>
            {{ paymentError }}
          </div>

          <div class="payment-layout">
            <!-- Plan -->
            <div class="plan-panel">
              <div class="plan-panel-label">Selected Plan</div>
              <h3>{{ subscription.plan?.name || '—' }}</h3>
              <div class="plan-panel-price">
                {{ subscription.plan?.currency || 'BDT' }}
                {{ formatPrice(subscription.plan?.price) }}
              </div>
              <div class="plan-panel-cycle">
                / {{ billingLabel(subscription.plan) }}
              </div>

              <div
                v-if="subscription.plan?.description"
                class="plan-panel-description"
              >
                {{ subscription.plan.description }}
              </div>

              <div
                v-if="subscription.plan?.features?.length"
                class="plan-feature-list"
              >
                <div
                  v-for="feature in subscription.plan.features"
                  :key="feature"
                >
                  <i class="bi bi-check-circle-fill"></i>
                  {{ feature }}
                </div>
              </div>
            </div>

            <!-- Form -->
            <form class="payment-form" @submit.prevent="handlePayment">
              <div class="form-section-title">Payment Information</div>

              <div class="mb-3">
                <label for="payment-method" class="form-label">
                  Payment Method <span class="text-danger">*</span>
                </label>
                <select
                  id="payment-method"
                  v-model="paymentForm.payment_method"
                  class="form-select"
                  :disabled="paymentSubmitting"
                >
                  <option value="">Select payment method</option>
                  <option
                    v-for="method in paymentMethods"
                    :key="method.id"
                    :value="method.code"
                  >
                    {{ method.name }}<template v-if="method.account_number"> — {{ method.account_number }}</template>
                  </option>
                </select>
                <div
                  v-if="fieldPaymentError('payment_method')"
                  class="field-error"
                >
                  {{ fieldPaymentError('payment_method') }}
                </div>
              </div>

              <div v-if="paymentForm.payment_method !== 'stripe'" class="mb-3">
                <label for="transaction-id" class="form-label">
                  Transaction ID
                  <span
                    v-if="paymentForm.payment_method === 'bkash' || paymentForm.payment_method === 'nagad'"
                    class="text-danger"
                  >*</span>
                </label>
                <input
                  id="transaction-id"
                  v-model="paymentForm.transaction_id"
                  type="text"
                  class="form-control"
                  placeholder="Enter transaction ID"
                  :disabled="paymentSubmitting"
                />
                <div
                  v-if="fieldPaymentError('transaction_id')"
                  class="field-error"
                >
                  {{ fieldPaymentError('transaction_id') }}
                </div>
              </div>

              <div v-if="paymentForm.payment_method !== 'stripe'" class="mb-3">
                <label for="payment-reference" class="form-label">
                  Payment Reference
                </label>
                <input
                  id="payment-reference"
                  v-model="paymentForm.reference"
                  type="text"
                  class="form-control"
                  placeholder="Enter payment reference"
                  :disabled="paymentSubmitting"
                />
                <div
                  v-if="fieldPaymentError('reference')"
                  class="field-error"
                >
                  {{ fieldPaymentError('reference') }}
                </div>
              </div>

              <div v-if="paymentForm.payment_method !== 'stripe'" class="mb-4">
                <label for="payment-notes" class="form-label">Notes</label>
                <textarea
                  id="payment-notes"
                  v-model="paymentForm.notes"
                  class="form-control"
                  rows="3"
                  placeholder="Optional payment notes"
                  :disabled="paymentSubmitting"
                ></textarea>
                <div
                  v-if="fieldPaymentError('notes')"
                  class="field-error"
                >
                  {{ fieldPaymentError('notes') }}
                </div>
              </div>

              <div class="payment-total">
                <span>Total Amount</span>
                <strong>
                  {{ subscription.plan?.currency || 'BDT' }}
                  {{ formatPrice(subscription.plan?.price) }}
                </strong>
              </div>

              <button
                type="submit"
                class="btn btn-success btn-lg w-100"
                :disabled="paymentSubmitting || !paymentMethods.length || !paymentForm.payment_method"
              >
                <span
                  v-if="paymentSubmitting"
                  class="spinner-border spinner-border-sm me-1"
                ></span>
                <i v-else class="bi bi-check-circle me-1"></i>
                {{ paymentSubmitting ? 'Processing Payment...' : 'Confirm Payment' }}
              </button>
            </form>
          </div>
        </div>
      </section>

      <!-- Payment History -->
      <section class="content-card">
        <div class="content-card-header">
          <div class="section-heading">
            <div class="section-icon">
              <i class="bi bi-receipt"></i>
            </div>
            <div>
              <h2>Payment History</h2>
              <p>All subscription payment and renewal transactions.</p>
            </div>
          </div>

          <span class="record-count">
            {{ payments.length }} record{{ payments.length === 1 ? '' : 's' }}
          </span>
        </div>

        <div class="content-card-body p-0">
          <div v-if="payments.length" class="table-responsive">
            <table class="table payment-table align-middle mb-0">
              <thead>
                <tr>
                  <th>Transaction</th>
                  <th>Method</th>
                  <th>Amount</th>
                  <th>Status</th>
                  <th>Paid At</th>
                </tr>
              </thead>
              <tbody>
                <tr
                  v-for="payment in paginatedPayments"
                  :key="payment.id"
                >
                  <td>
                    <div class="table-primary-text">
                      {{ payment.transaction_id || 'No transaction ID' }}
                    </div>
                    <div
                      v-if="payment.reference"
                      class="table-secondary-text"
                    >
                      {{ payment.reference }}
                    </div>
                  </td>

                  <td>
                    {{
                      payment.payment_method?.name ||
                      payment.payment_method_name ||
                      payment.payment_method ||
                      '—'
                    }}
                  </td>

                  <td>
                    <span class="amount-cell">
                      {{ payment.currency || 'BDT' }} {{ payment.amount || '0.00' }}
                    </span>
                  </td>

                  <td>
                    <span
                      class="status-badge"
                      :class="{
                        'status-success': payment.status === 'paid',
                        'status-warning': payment.status === 'pending',
                        'status-danger': payment.status === 'failed',
                        'status-muted': !['paid', 'pending', 'failed'].includes(payment.status)
                      }"
                    >
                      <span class="status-dot"></span>
                      {{ payment.status || 'unknown' }}
                    </span>
                  </td>

                  <td>
                    {{ formatDateTime(payment.paid_at || payment.created_at) }}
                  </td>
                </tr>
              </tbody>
            </table>
          </div>

          <PaginationControls
            v-if="payments.length"
            v-model:page="paymentsPage"
            :total="payments.length"
            :per-page="paymentPageSize"
          />

          <div v-else class="empty-state table-empty-state">
            <div class="empty-state-icon">
              <i class="bi bi-receipt-cutoff"></i>
            </div>
            <div>
              <strong>No payment history found</strong>
              <p>Payment transactions will appear here once available.</p>
            </div>
          </div>
        </div>
      </section>
    </template>

    <!-- Renewal Payment Modal -->
    <div
      v-if="showRenewalPayment"
      class="renewal-overlay"
      @click.self="closeRenewalPayment"
    >
      <div class="renewal-modal" role="dialog" aria-modal="true">
        <div class="renewal-modal-header">
          <div class="section-heading">
            <div class="section-icon">
              <i class="bi bi-arrow-repeat"></i>
            </div>
            <div>
              <h2>Renew Subscription</h2>
              <p>Extend the existing subscription without reprovisioning the tenant.</p>
            </div>
          </div>

          <button
            type="button"
            class="modal-close-btn"
            :disabled="renewalSubmitting"
            @click="closeRenewalPayment"
          >
            <i class="bi bi-x-lg"></i>
          </button>
        </div>

        <div class="renewal-modal-body">
          <div
            v-if="renewalError"
            class="alert alert-danger soft-alert"
            role="alert"
          >
            <i class="bi bi-exclamation-triangle-fill me-2"></i>
            {{ renewalError }}
          </div>

          <div
            v-if="renewalSuccess"
            class="alert alert-success soft-alert"
            role="alert"
          >
            <i class="bi bi-check-circle-fill me-2"></i>
            {{ renewalSuccess }}
          </div>

          <div class="renewal-plan-card">
            <div>
              <span class="info-label">Renewing Plan</span>
              <strong>{{ subscription?.plan?.name || '—' }}</strong>
            </div>
            <div class="renewal-plan-amount">
              <span class="info-label">Amount</span>
              <strong>
                {{ subscription?.plan?.currency || 'BDT' }}
                {{ formatPrice(subscription?.plan?.price) }}
              </strong>
            </div>
          </div>

          <div class="mb-3">
            <label for="renewal-payment-method" class="form-label">
              Payment Method <span class="text-danger">*</span>
            </label>
            <select
              id="renewal-payment-method"
              v-model="renewalForm.payment_method"
              class="form-select"
              :disabled="renewalSubmitting"
            >
              <option value="">Select payment method</option>
              <option
                v-for="method in paymentMethods"
                :key="method.id"
                :value="method.code"
              >
                {{ method.name }}
              </option>
            </select>
          </div>

          <div
            v-if="renewalForm.payment_method"
            class="payment-method-info"
          >
            <template
              v-for="method in paymentMethods"
              :key="method.id"
            >
              <div v-if="method.code === renewalForm.payment_method">
                <div v-if="method.account_number" class="method-info-row">
                  <span>Account</span>
                  <strong>{{ method.account_number }}</strong>
                </div>
                <div v-if="method.instructions" class="method-instructions">
                  {{ method.instructions }}
                </div>
              </div>
            </template>
          </div>

          <div v-if="renewalForm.payment_method !== 'stripe'" class="mb-3">
            <label for="renewal-transaction-id" class="form-label">
              Transaction ID
              <span
                v-if="renewalForm.payment_method === 'bkash' || renewalForm.payment_method === 'nagad'"
                class="text-danger"
              >*</span>
            </label>
            <input
              id="renewal-transaction-id"
              v-model="renewalForm.transaction_id"
              type="text"
              class="form-control"
              placeholder="Enter transaction ID"
              :disabled="renewalSubmitting"
            />
          </div>

          <div v-if="renewalForm.payment_method !== 'stripe'" class="mb-3">
            <label for="renewal-reference" class="form-label">Payment Reference</label>
            <input
              id="renewal-reference"
              v-model="renewalForm.reference"
              type="text"
              class="form-control"
              placeholder="Optional reference"
              :disabled="renewalSubmitting"
            />
          </div>

          <div v-if="renewalForm.payment_method !== 'stripe'">
            <label for="renewal-notes" class="form-label">Notes</label>
            <textarea
              id="renewal-notes"
              v-model="renewalForm.notes"
              class="form-control"
              rows="3"
              placeholder="Optional renewal notes"
              :disabled="renewalSubmitting"
            ></textarea>
          </div>
        </div>

        <div class="renewal-modal-footer">
          <button
            type="button"
            class="btn btn-light"
            :disabled="renewalSubmitting"
            @click="closeRenewalPayment"
          >
            Cancel
          </button>
          <button
            type="button"
            class="btn btn-primary"
            :disabled="renewalSubmitting || !renewalForm.payment_method"
            @click="handleRenewalPayment"
          >
            <span
              v-if="renewalSubmitting"
              class="spinner-border spinner-border-sm me-1"
            ></span>
            <i v-else class="bi bi-check-circle me-1"></i>
            {{ renewalSubmitting ? 'Processing...' : 'Complete Renewal' }}
          </button>
        </div>
      </div>
    </div>
    <!-- Trial to Paid Upgrade Modal -->
    <div
      v-if="showUpgradePayment"
      class="renewal-overlay"
      @click.self="closeUpgradePayment"
    >
      <div
        class="renewal-modal upgrade-modal"
        role="dialog"
        aria-modal="true"
        aria-labelledby="upgrade-trial-title"
      >
        <div class="renewal-modal-header">
          <div>
            <div class="d-flex align-items-center gap-2 mb-2">
              <span class="upgrade-icon">
                <i class="bi bi-lightning-charge"></i>
              </span>

              <h3
                id="upgrade-trial-title"
                class="mb-0"
              >
                {{
                  isExpiredTrialSubscription
                    ? 'Activate Paid Plan'
                    : 'Upgrade Trial'
                }}
              </h3>
            </div>

            <p class="text-muted mb-0">
              {{
                isExpiredTrialSubscription
                  ? 'Choose a paid plan and reactivate this expired trial restaurant.'
                  : 'Convert this active trial into a paid subscription.'
              }}
            </p>
          </div>

          <button
            type="button"
            class="modal-close-btn"
            :disabled="upgradeSubmitting"
            @click="closeUpgradePayment"
          >
            <i class="bi bi-x-lg"></i>
          </button>
        </div>

        <div class="renewal-modal-body">
          <div
            v-if="isExpiredTrialSubscription"
            class="alert alert-warning soft-alert mb-4"
            role="alert"
          >
            <i class="bi bi-info-circle-fill me-2"></i>
            This trial has expired. Completing this payment will start a new paid
            subscription period, reactivate the restaurant and restore tenant
            access. Existing restaurant data and the tenant database will be preserved.
          </div>

          <div class="upgrade-current-plan mb-4">
            <div>
              <span class="info-label">Current Plan</span>
              <strong>
                {{ subscription?.plan?.name || 'Trial' }}
              </strong>
            </div>

            <div class="upgrade-arrow">
              <i class="bi bi-arrow-right"></i>
            </div>

            <div>
              <span class="info-label">New Plan</span>
              <strong>
                {{
                  upgradeForm.subscription_plan_id
                    ? (
                        upgradePlans.find(
                          (plan) =>
                            Number(plan.id) ===
                            Number(upgradeForm.subscription_plan_id)
                        )?.name ||
                        'Select plan'
                      )
                    : 'Select plan'
                }}
              </strong>
            </div>
          </div>

          <div
            v-if="upgradeError"
            class="alert alert-danger soft-alert"
            role="alert"
          >
            <i class="bi bi-exclamation-triangle-fill me-2"></i>
            {{ upgradeError }}
          </div>

          <div
            v-if="upgradeSuccess"
            class="alert alert-success soft-alert"
            role="alert"
          >
            <i class="bi bi-check-circle-fill me-2"></i>
            {{ upgradeSuccess }}
          </div>

          <div class="mb-4">
            <label class="form-label fw-semibold">
              Select Paid Plan
            </label>

            <div class="upgrade-plan-grid">
              <button
                v-for="plan in upgradePlans"
                :key="plan.id"
                type="button"
                class="upgrade-plan-card"
                :class="{
                  selected:
                    Number(upgradeForm.subscription_plan_id) ===
                    Number(plan.id)
                }"
                :disabled="upgradeSubmitting"
                @click="upgradeForm.subscription_plan_id = plan.id"
              >
                <div class="d-flex justify-content-between align-items-start gap-2">
                  <div class="text-start">
                    <div class="fw-semibold">
                      {{ plan.name }}
                    </div>

                    <div class="small text-muted">
                      {{ plan.description || 'Paid subscription' }}
                    </div>
                  </div>

                  <i
                    v-if="
                      Number(upgradeForm.subscription_plan_id) ===
                      Number(plan.id)
                    "
                    class="bi bi-check-circle-fill text-success"
                  ></i>
                </div>

                <div class="upgrade-plan-price">
                  {{ plan.currency || 'BDT' }}
                  {{ formatPrice(plan.price) }}

                  <span class="small text-muted">
                    / {{ billingLabel(plan) }}
                  </span>
                </div>
              </button>
            </div>
          </div>

          <div class="mb-3">
            <label
              for="upgrade-payment-method"
              class="form-label fw-semibold"
            >
              Payment Method
            </label>

            <select
              id="upgrade-payment-method"
              v-model="upgradeForm.payment_method"
              class="form-select"
              :disabled="upgradeSubmitting"
            >
              <option value="">
                Select payment method
              </option>

              <option
                v-for="method in paymentMethods"
                :key="method.id"
                :value="method.code"
              >
                {{ method.name }}
              </option>
            </select>
          </div>

          <div
            v-if="upgradeForm.payment_method"
            class="payment-method-info mb-3"
          >
            <template
              v-for="method in paymentMethods"
              :key="method.id"
            >
              <div
                v-if="method.code === upgradeForm.payment_method"
              >
                <div
                  v-if="method.account_number"
                  class="method-info-row"
                >
                  <span>Account</span>
                  <strong>{{ method.account_number }}</strong>
                </div>

                <div
                  v-if="method.instructions"
                  class="method-instructions"
                >
                  {{ method.instructions }}
                </div>
              </div>
            </template>
          </div>

          <div v-if="upgradeForm.payment_method !== 'stripe'" class="mb-3">
            <label
              for="upgrade-transaction-id"
              class="form-label"
            >
              Transaction ID

              <span
                v-if="
                  paymentMethodRequiresTransaction(
                    upgradeForm.payment_method
                  )
                "
                class="text-danger"
              >
                *
              </span>
            </label>

            <input
              id="upgrade-transaction-id"
              v-model="upgradeForm.transaction_id"
              type="text"
              class="form-control"
              placeholder="Enter transaction ID"
              :disabled="upgradeSubmitting"
            />
          </div>

          <div v-if="upgradeForm.payment_method !== 'stripe'" class="mb-3">
            <label
              for="upgrade-reference"
              class="form-label"
            >
              Payment Reference
            </label>

            <input
              id="upgrade-reference"
              v-model="upgradeForm.reference"
              type="text"
              class="form-control"
              placeholder="Optional reference"
              :disabled="upgradeSubmitting"
            />
          </div>

          <div v-if="upgradeForm.payment_method !== 'stripe'">
            <label
              for="upgrade-notes"
              class="form-label"
            >
              Notes
            </label>

            <textarea
              id="upgrade-notes"
              v-model="upgradeForm.notes"
              class="form-control"
              rows="3"
              placeholder="Optional notes"
              :disabled="upgradeSubmitting"
            ></textarea>
          </div>
        </div>

        <div class="renewal-modal-footer">
          <button
            type="button"
            class="btn btn-light"
            :disabled="upgradeSubmitting"
            @click="closeUpgradePayment"
          >
            Cancel
          </button>

          <button
            type="button"
            class="btn btn-success"
            :disabled="
              upgradeSubmitting ||
              !upgradeForm.subscription_plan_id ||
              !upgradeForm.payment_method
            "
            @click="handleUpgradePayment"
          >
            <span
              v-if="upgradeSubmitting"
              class="spinner-border spinner-border-sm me-1"
            ></span>

            <template v-if="upgradeSubmitting">
              Processing...
            </template>

            <template v-else>
              <i
                :class="
                  isExpiredTrialSubscription
                    ? 'bi bi-arrow-clockwise me-1'
                    : 'bi bi-lightning-charge me-1'
                "
              ></i>

              {{
                isExpiredTrialSubscription
                  ? 'Activate & Complete Payment'
                  : 'Upgrade & Complete Payment'
              }}
            </template>
          </button>
        </div>
      </div>
    </div>

    <!-- Active Paid Plan Update Modal -->
    <div
      v-if="showPlanUpdate"
      class="renewal-overlay"
      @click.self="closePlanUpdate"
    >
      <div
        class="renewal-modal upgrade-modal"
        role="dialog"
        aria-modal="true"
        aria-labelledby="plan-update-title"
      >
        <div class="renewal-modal-header">
          <div>
            <div class="d-flex align-items-center gap-2 mb-2">
              <span class="upgrade-icon plan-update-icon">
                <i class="bi bi-arrow-repeat"></i>
              </span>

              <h3
                id="plan-update-title"
                class="mb-0"
              >
                Update Paid Plan
              </h3>
            </div>

            <p class="text-muted mb-0">
              Change the current active paid subscription without reprovisioning the tenant.
            </p>
          </div>

          <button
            type="button"
            class="modal-close-btn"
            :disabled="planUpdateSubmitting"
            @click="closePlanUpdate"
          >
            <i class="bi bi-x-lg"></i>
          </button>
        </div>

        <div class="renewal-modal-body">
          <div class="upgrade-current-plan mb-4">
            <div>
              <span class="info-label">Current Plan</span>
              <strong>
                {{ subscription?.plan?.name || '—' }}
              </strong>
            </div>

            <div class="upgrade-arrow">
              <i class="bi bi-arrow-right"></i>
            </div>

            <div>
              <span class="info-label">New Plan</span>
              <strong>
                {{
                  planUpdateForm.subscription_plan_id
                    ? (
                        planUpdatePlans.find(
                          (plan) =>
                            Number(plan.id) ===
                            Number(planUpdateForm.subscription_plan_id)
                        )?.name ||
                        'Select plan'
                      )
                    : 'Select plan'
                }}
              </strong>
            </div>
          </div>

          <div
            v-if="planUpdateError"
            class="alert alert-danger soft-alert"
            role="alert"
          >
            <i class="bi bi-exclamation-triangle-fill me-2"></i>
            {{ planUpdateError }}
          </div>

          <div
            v-if="planUpdateSuccess"
            class="alert alert-success soft-alert"
            role="alert"
          >
            <i class="bi bi-check-circle-fill me-2"></i>
            {{ planUpdateSuccess }}
          </div>

          <div class="mb-4">
            <label class="form-label fw-semibold">
              Select Paid Plan
            </label>

            <div class="upgrade-plan-grid">
              <button
                v-for="plan in planUpdatePlans"
                :key="plan.id"
                type="button"
                class="upgrade-plan-card"
                :class="{
                  selected:
                    Number(planUpdateForm.subscription_plan_id) ===
                    Number(plan.id),
                  'current-plan':
                    Number(subscription?.plan?.id) === Number(plan.id)
                }"
                :disabled="
                  planUpdateSubmitting ||
                  Number(subscription?.plan?.id) === Number(plan.id)
                "
                @click="planUpdateForm.subscription_plan_id = plan.id"
              >
                <div class="d-flex justify-content-between align-items-start gap-2">
                  <div class="text-start">
                    <div class="fw-semibold">
                      {{ plan.name }}
                    </div>

                    <div class="small text-muted">
                      {{
                        Number(subscription?.plan?.id) === Number(plan.id)
                          ? 'Current active plan'
                          : (plan.description || 'Paid subscription')
                      }}
                    </div>
                  </div>

                  <i
                    v-if="
                      Number(planUpdateForm.subscription_plan_id) ===
                      Number(plan.id)
                    "
                    class="bi bi-check-circle-fill text-primary"
                  ></i>
                </div>

                <div class="upgrade-plan-price">
                  {{ plan.currency || 'BDT' }}
                  {{ formatPrice(plan.price) }}

                  <span class="small text-muted">
                    / {{ billingLabel(plan) }}
                  </span>
                </div>
              </button>
            </div>
          </div>

          <div class="mb-3">
            <label
              for="plan-update-payment-method"
              class="form-label fw-semibold"
            >
              Payment Method
            </label>

            <select
              id="plan-update-payment-method"
              v-model="planUpdateForm.payment_method"
              class="form-select"
              :disabled="planUpdateSubmitting"
            >
              <option value="">
                Select payment method
              </option>

              <option
                v-for="method in paymentMethods"
                :key="method.id"
                :value="method.code"
              >
                {{ method.name }}
              </option>
            </select>
          </div>

          <div
            v-if="planUpdateForm.payment_method"
            class="payment-method-info mb-3"
          >
            <template
              v-for="method in paymentMethods"
              :key="method.id"
            >
              <div
                v-if="method.code === planUpdateForm.payment_method"
              >
                <div
                  v-if="method.account_number"
                  class="method-info-row"
                >
                  <span>Account</span>
                  <strong>{{ method.account_number }}</strong>
                </div>

                <div
                  v-if="method.instructions"
                  class="method-instructions"
                >
                  {{ method.instructions }}
                </div>
              </div>
            </template>
          </div>

          <div v-if="planUpdateForm.payment_method !== 'stripe'" class="mb-3">
            <label
              for="plan-update-transaction-id"
              class="form-label"
            >
              Transaction ID
              <span
                v-if="paymentMethodRequiresTransaction(planUpdateForm.payment_method)"
                class="text-danger"
              >*</span>
            </label>

            <input
              id="plan-update-transaction-id"
              v-model="planUpdateForm.transaction_id"
              type="text"
              class="form-control"
              placeholder="Enter transaction ID"
              :disabled="planUpdateSubmitting"
            />
          </div>

          <div v-if="planUpdateForm.payment_method !== 'stripe'" class="mb-3">
            <label
              for="plan-update-reference"
              class="form-label"
            >
              Payment Reference
            </label>

            <input
              id="plan-update-reference"
              v-model="planUpdateForm.reference"
              type="text"
              class="form-control"
              placeholder="Optional reference"
              :disabled="planUpdateSubmitting"
            />
          </div>

          <div v-if="planUpdateForm.payment_method !== 'stripe'">
            <label
              for="plan-update-notes"
              class="form-label"
            >
              Notes
            </label>

            <textarea
              id="plan-update-notes"
              v-model="planUpdateForm.notes"
              class="form-control"
              rows="3"
              placeholder="Optional plan update notes"
              :disabled="planUpdateSubmitting"
            ></textarea>
          </div>
        </div>

        <div class="renewal-modal-footer">
          <button
            type="button"
            class="btn btn-light"
            :disabled="planUpdateSubmitting"
            @click="closePlanUpdate"
          >
            Cancel
          </button>

          <button
            type="button"
            class="btn btn-primary"
            :disabled="
              planUpdateSubmitting ||
              !planUpdateForm.subscription_plan_id ||
              !planUpdateForm.payment_method
            "
            @click="handlePlanUpdatePayment"
          >
            <span
              v-if="planUpdateSubmitting"
              class="spinner-border spinner-border-sm me-1"
            ></span>

            <template v-if="planUpdateSubmitting">
              Processing...
            </template>

            <template v-else>
              <i class="bi bi-arrow-repeat me-1"></i>
              Update Plan & Complete Payment
            </template>
          </button>
        </div>
      </div>
    </div>
  </section>
</template>


<script setup>

import '@/styles/super-admin/restaurant-details.css'
import '@/styles/super-admin/client-access.css'
import '@/styles/super-admin/client-handover.css'

import {
  computed,
  onBeforeUnmount,
  onMounted,
  ref,
} from 'vue'
import PaginationControls from '@/components/shared/PaginationControls.vue'

import {
  useRoute,
} from 'vue-router'

import {
  getRestaurant,
  getSubscription,
  getPaymentHistory,
  getPaymentMethods,
  completeRestaurantPayment,
  createStripeCheckout,
  confirmStripePayment,
  resetTenantAdminCredentials,
  updateSubscriptionStatus,
  completeSubscriptionRenewal,
  getSubscriptionPlans,
  upgradeTrialToPaid,
  updatePaidSubscriptionPlan,
} from '@/services/superAdminApi'


/*
|--------------------------------------------------------------------------
| Router
|--------------------------------------------------------------------------
*/

const route = useRoute()


/*
|--------------------------------------------------------------------------
| General State
|--------------------------------------------------------------------------
*/

const loading = ref(false)

const error = ref('')

const restaurant = ref(null)

const subscription = ref(null)

const payments = ref([])
const paymentsPage = ref(1)
const paymentPageSize = 10
const paginatedPayments = computed(() =>
  payments.value.slice(
    (paymentsPage.value - 1) * paymentPageSize,
    paymentsPage.value * paymentPageSize,
  ),
)

/*
|--------------------------------------------------------------------------
| Live Lifecycle Clock
|--------------------------------------------------------------------------
|
| Keeps time-sensitive UI reactive even while the details page stays open.
|
*/

const currentTime = ref(Date.now())


/*
|--------------------------------------------------------------------------
| Tenant Database / Domain
|--------------------------------------------------------------------------
*/

const tenantDatabase = computed(() => {
  return restaurant.value?.tenant_database ?? null
})


const primaryDomain = computed(() => {
  const domains = restaurant.value?.domains

  if (!Array.isArray(domains)) {
    return null
  }

  return (
    domains.find((domain) => Boolean(domain.is_primary)) ||
    domains[0] ||
    null
  )
})


/*
|--------------------------------------------------------------------------
| Client Access / Domain Presentation
|--------------------------------------------------------------------------
*/

const clientAccessCopied = ref(false)

const tenantKeyCopied = ref(false)

let clientAccessCopyTimer = null

let tenantKeyCopyTimer = null


/*
|--------------------------------------------------------------------------
| Client Handover
|--------------------------------------------------------------------------
*/

const handoverCopied = ref(false)

const handoverSharing = ref(false)

const handoverMessage = ref('')

const handoverError = ref('')

let handoverCopyTimer = null


const hasHandoverCredentials = computed(() => {

  return Boolean(
    adminCredentials.value?.username &&
    adminCredentials.value?.password
  )
})


const canShareHandover = computed(() => {

  return (
    typeof navigator !== 'undefined' &&
    typeof navigator.share === 'function'
  )
})


const handoverText = computed(() => {

  if (
    !hasHandoverCredentials.value
  ) {
    return ''
  }


  const restaurantName =
    restaurant.value?.name ||
    'Restaurant'


  return [
    `${restaurantName} — RMS Access`,
    '',
    `Login URL: ${getTenantUrl()}`,
    `Username: ${adminCredentials.value.username}`,
    `Temporary Password: ${adminCredentials.value.password}`,
    '',
    'Please keep these credentials secure.',
  ].join('\n')
})


const isLocalEnvironment = computed(() => {

  if (
    typeof window === 'undefined'
  ) {
    return false
  }

  const hostname =
    String(
      window.location.hostname || ''
    ).toLowerCase()

  return [
    'localhost',
    '127.0.0.1',
    '::1',
  ].includes(hostname)
})


const isLocalTechnicalDomain = computed(() => {

  const domain =
    String(
      primaryDomain.value?.domain || ''
    )
      .trim()
      .toLowerCase()

  return (
    domain.endsWith('.local') ||
    domain === 'local'
  )
})


/*
|--------------------------------------------------------------------------
| Subscription Lifecycle UI State
|--------------------------------------------------------------------------
*/

const subscriptionHasEnded = computed(() => {
  const endAt = subscription.value?.end_at

  if (!endAt) {
    return false
  }

  const endTimestamp =
    new Date(endAt).getTime()

  if (Number.isNaN(endTimestamp)) {
    return false
  }

  return endTimestamp <= currentTime.value
})


const canOpenRestaurant = computed(() => {
  return (
    Boolean(restaurant.value) &&
    restaurant.value.status === 'active' &&
    Boolean(restaurant.value.tenant_key) &&
    subscription.value?.status === 'active' &&
    !subscriptionHasEnded.value
  )
})


/*
|--------------------------------------------------------------------------
| Trial Upgrade Eligibility
|--------------------------------------------------------------------------
*/

const isTrialSubscription = computed(() => {
  const currentSubscription =
    subscription.value

  const currentPlan =
    currentSubscription?.plan

  if (!currentSubscription || !currentPlan) {
    return false
  }

  const trialEndTimestamp =
    currentSubscription.trial_end_at
      ? new Date(
          currentSubscription.trial_end_at,
        ).getTime()
      : Number.NaN

  return (
    currentSubscription.status === 'active' &&
    !subscriptionHasEnded.value &&
    !Number.isNaN(trialEndTimestamp) &&
    trialEndTimestamp > currentTime.value &&
    Number(currentPlan.price || 0) <= 0
  )
})


/*
|--------------------------------------------------------------------------
| Expired Trial Paid Activation
|--------------------------------------------------------------------------
|
| Expired trial restaurants are intentionally inactive, but Super Admin must
| still be able to choose a paid plan and reactivate them through payment.
|
*/

const isExpiredTrialSubscription = computed(() => {
  const currentSubscription =
    subscription.value

  const currentPlan =
    currentSubscription?.plan

  if (
    !currentSubscription ||
    !currentPlan
  ) {
    return false
  }


  const trialEndTimestamp =
    currentSubscription.trial_end_at
      ? new Date(
          currentSubscription.trial_end_at,
        ).getTime()
      : Number.NaN


  return (
    currentSubscription.status === 'expired' &&
    Number(currentPlan.price || 0) <= 0 &&
    !Number.isNaN(trialEndTimestamp) &&
    trialEndTimestamp <= currentTime.value
  )
})


const isTrialPaidActivationFlow = computed(() => {
  return (
    isTrialSubscription.value ||
    isExpiredTrialSubscription.value
  )
})


const isPaidSubscription = computed(() => {
  const currentPlan =
    subscription.value?.plan

  return (
    Boolean(currentPlan) &&
    Number(currentPlan.price || 0) > 0 &&
    subscription.value?.status === 'active' &&
    !subscriptionHasEnded.value
  )
})


/*
|--------------------------------------------------------------------------
| Tenant Admin Credentials
|--------------------------------------------------------------------------
*/

const adminCredentials = ref(null)

const credentialsLoading = ref(false)

const credentialsMessage = ref('')

const credentialsError = ref('')


/*
|--------------------------------------------------------------------------
| Subscription Status
|--------------------------------------------------------------------------
*/

const selectedSubscriptionStatus = ref('')

const subscriptionUpdating = ref(false)

const subscriptionMessage = ref('')

const subscriptionError = ref('')

const subscriptionCancelReason = ref('')


/*
|--------------------------------------------------------------------------
| Initial Payment
|--------------------------------------------------------------------------
*/

const paymentMethods = ref([])

const paymentSubmitting = ref(false)

const paymentMessage = ref('')

const paymentError = ref('')

const paymentValidationErrors = ref({})
const stripeReturnMessage = ref('')
const stripeReturnError = ref('')

const paymentForm = ref({
  payment_method: '',
  transaction_id: '',
  reference: '',
  notes: '',
})


/*
|--------------------------------------------------------------------------
| Trial Upgrade State
|--------------------------------------------------------------------------
*/

const showUpgradePayment = ref(false)

const upgradeSubmitting = ref(false)

const upgradeError = ref('')

const upgradeSuccess = ref('')

const upgradePlans = ref([])

const upgradeForm = ref({
  subscription_plan_id: '',
  payment_method: '',
  transaction_id: '',
  reference: '',
  notes: '',
})


/*
|--------------------------------------------------------------------------
| Active Paid Plan Update State
|--------------------------------------------------------------------------
*/

const showPlanUpdate = ref(false)

const planUpdateSubmitting = ref(false)

const planUpdateError = ref('')

const planUpdateSuccess = ref('')

const planUpdatePlans = ref([])

const planUpdateForm = ref({
  subscription_plan_id: '',
  payment_method: '',
  transaction_id: '',
  reference: '',
  notes: '',
})


/*
|--------------------------------------------------------------------------
| Renewal Payment
|--------------------------------------------------------------------------
*/

const showRenewalPayment = ref(false)

const renewalSubmitting = ref(false)

const renewalError = ref('')

const renewalSuccess = ref('')

const renewalForm = ref({
  payment_method: '',
  transaction_id: '',
  reference: '',
  notes: '',
})


/*
|--------------------------------------------------------------------------
| Load Restaurant
|--------------------------------------------------------------------------
*/

async function loadRestaurant() {
  const response = await getRestaurant(route.params.restaurant)

  if (!response?.success) {
    throw new Error(
      response?.message ||
      'Failed to load restaurant details.',
    )
  }

  const restaurantData = response.data?.restaurant ?? {}

  restaurant.value = {
    ...restaurantData,

    tenant_database:
      response.data?.tenant_database ??
      restaurantData.tenant_database ??
      null,

    domains:
      Array.isArray(response.data?.domains)
        ? response.data.domains
        : Array.isArray(restaurantData.domains)
          ? restaurantData.domains
          : [],

    subscriptions:
      Array.isArray(response.data?.subscriptions)
        ? response.data.subscriptions
        : Array.isArray(restaurantData.subscriptions)
          ? restaurantData.subscriptions
          : [],

    /*
     * Keep singular subscription in sync when the restaurant
     * endpoint already provides it.
     */
    subscription:
      response.data?.subscription ??
      restaurantData.subscription ??
      null,
  }
}


/*
|--------------------------------------------------------------------------
| Load Subscription
|--------------------------------------------------------------------------
*/

async function loadSubscription() {
  const response = await getSubscription(
    route.params.restaurant,
  )

  if (!response?.success) {
    throw new Error(
      response?.message ||
      'Failed to load subscription.',
    )
  }

  const currentSubscription =
    response.data?.subscription ??
    response.data ??
    null

  subscription.value = currentSubscription

  selectedSubscriptionStatus.value =
    currentSubscription?.status || ''

  subscriptionCancelReason.value =
    currentSubscription?.cancel_reason || ''

  /*
   * The template uses both:
   * - subscription
   * - restaurant.subscription
   *
   * Keep both references synchronized.
   */
  if (restaurant.value) {
    restaurant.value = {
      ...restaurant.value,
      subscription: currentSubscription,
    }
  }
}


/*
|--------------------------------------------------------------------------
| Load Payment History
|--------------------------------------------------------------------------
*/

async function loadPayments() {
  const response = await getPaymentHistory(
    route.params.restaurant,
  )

  if (!response?.success) {
    throw new Error(
      response?.message ||
      'Failed to load payment history.',
    )
  }

  const data = response.data

  if (Array.isArray(data)) {
    payments.value = data
    paymentsPage.value = 1
    return
  }

  if (Array.isArray(data?.payments)) {
    payments.value = data.payments
    paymentsPage.value = 1
    return
  }

  payments.value = []
  paymentsPage.value = 1
}


/*
|--------------------------------------------------------------------------
| Load Payment Methods
|--------------------------------------------------------------------------
*/

async function loadPaymentMethods() {
  const response = await getPaymentMethods()

  if (!response?.success) {
    throw new Error(
      response?.message ||
      'Failed to load payment methods.',
    )
  }

  const data = response.data

  if (Array.isArray(data)) {
    paymentMethods.value = data
    return
  }

  if (Array.isArray(data?.payment_methods)) {
    paymentMethods.value = data.payment_methods
    return
  }

  paymentMethods.value = []
}


/*
|--------------------------------------------------------------------------
| Payment Validation
|--------------------------------------------------------------------------
*/

function fieldPaymentError(field) {
  const errors =
    paymentValidationErrors.value?.[field]

  if (Array.isArray(errors) && errors.length) {
    return errors[0]
  }

  return ''
}


function paymentMethodRequiresTransaction(
  code,
) {
  return ['bkash', 'nagad'].includes(
    String(code || '').toLowerCase(),
  )
}


/*
|--------------------------------------------------------------------------
| Complete Initial Payment
|--------------------------------------------------------------------------
*/

async function handlePayment() {
  paymentMessage.value = ''
  paymentError.value = ''
  paymentValidationErrors.value = {}

  if (!restaurant.value?.id) {
    paymentError.value =
      'Restaurant could not be identified.'
    return
  }

  if (!subscription.value?.id) {
    paymentError.value =
      'Pending subscription could not be identified.'
    return
  }

  const paymentMethod =
    String(
      paymentForm.value.payment_method || '',
    ).trim()

  if (!paymentMethod) {
    paymentError.value =
      'Please select a payment method.'
    return
  }

  if (
    paymentMethodRequiresTransaction(
      paymentMethod,
    ) &&
    !paymentForm.value.transaction_id.trim()
  ) {
    paymentError.value =
      'Transaction ID is required for bKash or Nagad payments.'
    return
  }

  paymentSubmitting.value = true

  try {
    if (paymentMethod === 'stripe') {
      const checkout = await createStripeCheckout({
        payment_type: 'initial',
        restaurant_id: Number(restaurant.value.id),
        subscription_id: Number(subscription.value.id),
      })
      window.location.assign(checkout.data.checkout_url)
      return
    }

    const payload = {
      subscription_id:
        Number(subscription.value.id),

      payment_method:
        paymentMethod,

      transaction_id:
        paymentForm.value.transaction_id.trim() ||
        null,

      reference:
        paymentForm.value.reference.trim() ||
        null,

      notes:
        paymentForm.value.notes.trim() ||
        null,
    }

    const response =
      await completeRestaurantPayment(
        restaurant.value.id,
        payload,
      )

    if (!response?.success) {
      throw new Error(
        response?.message ||
        'Failed to complete payment.',
      )
    }

    paymentMessage.value =
      response.message ||
      'Payment completed successfully. Restaurant is now active.'

    /*
     * Provisioning response may return tenant admin credentials.
     * Show them once in the UI.
     */
    adminCredentials.value =
      response.data?.admin ?? null

    /*
     * Update local restaurant immediately.
     */
    if (response.data?.restaurant) {
      restaurant.value = {
        ...restaurant.value,
        ...response.data.restaurant,
      }
    }

    /*
     * Reset form.
     */
    resetPaymentForm()

    /*
     * Reload all fresh central data.
     */
    await loadAll()

  } catch (error) {
    paymentError.value =
      error?.response?.data?.message ||
      error?.message ||
      'Failed to complete payment.'

    paymentValidationErrors.value =
      error?.response?.data?.errors ||
      error?.validationErrors ||
      {}
  } finally {
    paymentSubmitting.value = false
  }
}


/*
|--------------------------------------------------------------------------
| Reset Initial Payment Form
|--------------------------------------------------------------------------
*/

function resetPaymentForm() {
  paymentForm.value = {
    payment_method: '',
    transaction_id: '',
    reference: '',
    notes: '',
  }

  paymentValidationErrors.value = {}
}


/*
|--------------------------------------------------------------------------
| Subscription Status Update
|--------------------------------------------------------------------------
*/

async function handleSubscriptionStatusUpdate() {
  subscriptionMessage.value = ''
  subscriptionError.value = ''

  if (!restaurant.value?.id) {
    subscriptionError.value =
      'Restaurant could not be identified.'
    return
  }

  if (!subscription.value?.id) {
    subscriptionError.value =
      'Subscription could not be identified.'
    return
  }

  const nextStatus =
    selectedSubscriptionStatus.value

  if (!nextStatus) {
    subscriptionError.value =
      'Please select a subscription status.'
    return
  }

  if (nextStatus === subscription.value.status) {
    subscriptionError.value =
      'Please select a different subscription status.'
    return
  }

  if (
    nextStatus === 'cancelled' &&
    !subscriptionCancelReason.value.trim()
  ) {
    subscriptionError.value =
      'A cancellation reason is required.'
    return
  }

  let confirmationMessage =
    `Change subscription status to "${nextStatus}"?`

  if (nextStatus === 'cancelled') {
    confirmationMessage =
      'Cancel this subscription? The restaurant will become inactive.'
  } else if (nextStatus === 'expired') {
    confirmationMessage =
      'Mark this subscription as expired? The restaurant will become inactive.'
  } else if (nextStatus === 'active') {
    confirmationMessage =
      'Activate this subscription and restaurant?'
  }

  if (!window.confirm(confirmationMessage)) {
    return
  }

  subscriptionUpdating.value = true

  try {
    const response =
      await updateSubscriptionStatus(
        restaurant.value.id,
        nextStatus,
        nextStatus === 'cancelled'
          ? subscriptionCancelReason.value.trim()
          : null,
      )

    if (!response?.success) {
      throw new Error(
        response?.message ||
        'Failed to update subscription status.',
      )
    }

    subscriptionMessage.value =
      response.message ||
      'Subscription status updated successfully.'

    if (response.data?.subscription) {
      subscription.value =
        response.data.subscription

      selectedSubscriptionStatus.value =
        subscription.value.status

      if (restaurant.value) {
        restaurant.value = {
          ...restaurant.value,
          subscription:
            response.data.subscription,
        }
      }
    }

    if (response.data?.restaurant) {
      restaurant.value = {
        ...restaurant.value,
        ...response.data.restaurant,
      }
    }

    if (
      selectedSubscriptionStatus.value !==
      'cancelled'
    ) {
      subscriptionCancelReason.value = ''
    }

    /*
     * Fetch authoritative data from backend.
     */
    await loadRestaurant()
    await loadSubscription()

  } catch (error) {
    subscriptionError.value =
      error?.response?.data?.message ||
      error?.message ||
      'Failed to update subscription status.'
  } finally {
    subscriptionUpdating.value = false
  }
}


/*
|--------------------------------------------------------------------------
| Load Paid Upgrade Plans
|--------------------------------------------------------------------------
*/

async function loadUpgradePlans() {
  const response =
    await getSubscriptionPlans()

  if (!response?.success) {
    throw new Error(
      response?.message ||
      'Failed to load subscription plans.',
    )
  }

  const data = response.data

  const plans =
    Array.isArray(data)
      ? data
      : Array.isArray(data?.plans)
        ? data.plans
        : []

  upgradePlans.value =
    plans.filter(
      (plan) =>
        Boolean(plan.is_active) &&
        Number(plan.price || 0) > 0,
    )
}


/*
|--------------------------------------------------------------------------
| Open Trial Upgrade
|--------------------------------------------------------------------------
*/

async function openUpgradePayment() {
  upgradeError.value = ''
  upgradeSuccess.value = ''

  upgradeForm.value = {
    subscription_plan_id: '',
    payment_method: '',
    transaction_id: '',
    reference: '',
    notes: '',
  }

  /*
   * Open first so any loading/API error is visible
   * inside the modal.
   */
  showUpgradePayment.value = true

  try {
    await loadUpgradePlans()

    if (!upgradePlans.value.length) {
      upgradeError.value =
        'No paid subscription plans are currently available.'
    }

  } catch (error) {
    upgradeError.value =
      error?.response?.data?.message ||
      error?.message ||
      'Unable to load paid subscription plans.'
  }
}


/*
|--------------------------------------------------------------------------
| Close Trial Upgrade
|--------------------------------------------------------------------------
*/

function closeUpgradePayment() {
  if (upgradeSubmitting.value) {
    return
  }

  showUpgradePayment.value = false

  upgradeError.value = ''
  upgradeSuccess.value = ''

  upgradePlans.value = []

  upgradeForm.value = {
    subscription_plan_id: '',
    payment_method: '',
    transaction_id: '',
    reference: '',
    notes: '',
  }
}


/*
|--------------------------------------------------------------------------
| Complete Trial Upgrade
|--------------------------------------------------------------------------
*/

async function handleUpgradePayment() {
  upgradeError.value = ''
  upgradeSuccess.value = ''

  if (!restaurant.value?.id) {
    upgradeError.value =
      'Restaurant information is missing.'
    return
  }

  if (!subscription.value?.id) {
    upgradeError.value =
      'Subscription information is missing.'
    return
  }

  if (!isTrialPaidActivationFlow.value) {
    upgradeError.value =
      'This subscription is not eligible for trial upgrade or expired-trial activation.'
    return
  }

  if (!upgradeForm.value.subscription_plan_id) {
    upgradeError.value =
      'Please select a paid subscription plan.'
    return
  }

  if (!upgradeForm.value.payment_method) {
    upgradeError.value =
      'Please select a payment method.'
    return
  }

  const selectedMethod =
    paymentMethods.value.find(
      (method) =>
        method.code ===
        upgradeForm.value.payment_method,
    )

  const requiresTransactionId =
    paymentMethodRequiresTransaction(
      selectedMethod?.code,
    )

  if (
    requiresTransactionId &&
    !upgradeForm.value.transaction_id.trim()
  ) {
    upgradeError.value =
      'Transaction ID is required for bKash or Nagad payments.'
    return
  }

  const selectedPlan =
    upgradePlans.value.find(
      (plan) =>
        Number(plan.id) ===
        Number(
          upgradeForm.value.subscription_plan_id,
        ),
    )

  if (!selectedPlan) {
    upgradeError.value =
      'Selected paid plan is not available.'
    return
  }

  if (Number(selectedPlan.price || 0) <= 0) {
    upgradeError.value =
      'Please select a paid plan.'
    return
  }

  upgradeSubmitting.value = true

  try {
    if (upgradeForm.value.payment_method === 'stripe') {
      const checkout = await createStripeCheckout({
        payment_type: 'upgrade',
        restaurant_id: Number(restaurant.value.id),
        subscription_id: Number(subscription.value.id),
        subscription_plan_id: Number(upgradeForm.value.subscription_plan_id),
      })
      window.location.assign(checkout.data.checkout_url)
      return
    }

    const response =
      await upgradeTrialToPaid(
        restaurant.value.id,
        {
          subscription_id:
            Number(subscription.value.id),

          subscription_plan_id:
            Number(
              upgradeForm.value.subscription_plan_id,
            ),

          payment_method:
            upgradeForm.value.payment_method,

          transaction_id:
            upgradeForm.value.transaction_id.trim() ||
            null,

          reference:
            upgradeForm.value.reference.trim() ||
            null,

          notes:
            upgradeForm.value.notes.trim() ||
            null,
        },
      )

    if (!response?.success) {
      throw new Error(
        response?.message ||
        'Trial upgrade failed.',
      )
    }

    upgradeSuccess.value =
      response.message ||
      (
        isExpiredTrialSubscription.value
          ? 'Expired trial successfully activated with the paid subscription.'
          : 'Trial successfully upgraded to the paid subscription.'
      )

    await loadAll()

    window.setTimeout(() => {
      if (!upgradeSubmitting.value) {
        closeUpgradePayment()
      }
    }, 700)

  } catch (error) {
    upgradeError.value =
      error?.response?.data?.message ||
      error?.message ||
      'Unable to complete trial upgrade.'
  } finally {
    upgradeSubmitting.value = false
  }
}


/*
|--------------------------------------------------------------------------
| Load Paid Plans For Active Plan Update
|--------------------------------------------------------------------------
*/

async function loadPlanUpdatePlans() {
  const response =
    await getSubscriptionPlans()

  if (!response?.success) {
    throw new Error(
      response?.message ||
      'Failed to load subscription plans.',
    )
  }

  const data = response.data

  const plans =
    Array.isArray(data)
      ? data
      : Array.isArray(data?.plans)
        ? data.plans
        : []

  planUpdatePlans.value =
    plans.filter(
      (plan) =>
        Boolean(plan.is_active) &&
        Number(plan.price || 0) > 0,
    )
}


/*
|--------------------------------------------------------------------------
| Open Active Paid Plan Update
|--------------------------------------------------------------------------
*/

async function openPlanUpdate() {
  planUpdateError.value = ''
  planUpdateSuccess.value = ''

  planUpdateForm.value = {
    subscription_plan_id: '',
    payment_method: '',
    transaction_id: '',
    reference: '',
    notes: '',
  }

  showPlanUpdate.value = true

  try {
    await loadPlanUpdatePlans()

    if (!planUpdatePlans.value.length) {
      planUpdateError.value =
        'No paid subscription plans are available.'
    }
  } catch (error) {
    planUpdateError.value =
      error?.response?.data?.message ||
      error?.message ||
      'Unable to load paid subscription plans.'
  }
}


/*
|--------------------------------------------------------------------------
| Close Active Paid Plan Update
|--------------------------------------------------------------------------
*/

function closePlanUpdate() {
  if (planUpdateSubmitting.value) {
    return
  }

  showPlanUpdate.value = false
  planUpdateError.value = ''
  planUpdateSuccess.value = ''
  planUpdatePlans.value = []

  planUpdateForm.value = {
    subscription_plan_id: '',
    payment_method: '',
    transaction_id: '',
    reference: '',
    notes: '',
  }
}


/*
|--------------------------------------------------------------------------
| Complete Active Paid Plan Update
|--------------------------------------------------------------------------
*/

async function handlePlanUpdatePayment() {
  planUpdateError.value = ''
  planUpdateSuccess.value = ''

  if (!restaurant.value?.id) {
    planUpdateError.value =
      'Restaurant information is missing.'
    return
  }

  if (!subscription.value?.id) {
    planUpdateError.value =
      'Subscription information is missing.'
    return
  }

  if (!isPaidSubscription.value) {
    planUpdateError.value =
      'Only an active paid subscription can use this plan update flow.'
    return
  }

  if (!planUpdateForm.value.subscription_plan_id) {
    planUpdateError.value =
      'Please select a paid subscription plan.'
    return
  }

  if (!planUpdateForm.value.payment_method) {
    planUpdateError.value =
      'Please select a payment method.'
    return
  }

  const selectedPlan =
    planUpdatePlans.value.find(
      (plan) =>
        Number(plan.id) ===
        Number(
          planUpdateForm.value.subscription_plan_id,
        ),
    )

  if (!selectedPlan) {
    planUpdateError.value =
      'Selected paid plan is not available.'
    return
  }

  if (
    Number(selectedPlan.id) ===
    Number(subscription.value.plan?.id)
  ) {
    planUpdateError.value =
      'The selected plan is already active.'
    return
  }

  const paymentMethod =
    String(
      planUpdateForm.value.payment_method || '',
    ).trim()

  if (
    paymentMethodRequiresTransaction(
      paymentMethod,
    ) &&
    !planUpdateForm.value.transaction_id.trim()
  ) {
    planUpdateError.value =
      'Transaction ID is required for bKash or Nagad payments.'
    return
  }

  if (
    !window.confirm(
      `Change this restaurant from ${
        subscription.value.plan?.name || 'Current Plan'
      } to ${selectedPlan.name} and record payment?`,
    )
  ) {
    return
  }

  planUpdateSubmitting.value = true

  try {
    if (paymentMethod === 'stripe') {
      const checkout = await createStripeCheckout({
        payment_type: 'plan_update',
        restaurant_id: Number(restaurant.value.id),
        subscription_id: Number(subscription.value.id),
        subscription_plan_id: Number(selectedPlan.id),
      })
      window.location.assign(checkout.data.checkout_url)
      return
    }

    const response =
      await updatePaidSubscriptionPlan(
        restaurant.value.id,
        {
          subscription_id:
            Number(subscription.value.id),

          subscription_plan_id:
            Number(selectedPlan.id),

          payment_method:
            paymentMethod,

          transaction_id:
            planUpdateForm.value.transaction_id.trim() ||
            null,

          reference:
            planUpdateForm.value.reference.trim() ||
            null,

          notes:
            planUpdateForm.value.notes.trim() ||
            null,
        },
      )

    if (!response?.success) {
      throw new Error(
        response?.message ||
        'Subscription plan update failed.',
      )
    }

    planUpdateSuccess.value =
      response.message ||
      'Subscription plan updated successfully.'

    await loadAll()

    window.setTimeout(() => {
      if (!planUpdateSubmitting.value) {
        closePlanUpdate()
      }
    }, 700)
  } catch (error) {
    planUpdateError.value =
      error?.response?.data?.message ||
      error?.message ||
      'Unable to update the subscription plan.'
  } finally {
    planUpdateSubmitting.value = false
  }
}


/*
|--------------------------------------------------------------------------
| Renewal Modal
|--------------------------------------------------------------------------
*/

function openRenewalPayment() {
  renewalError.value = ''
  renewalSuccess.value = ''

  renewalForm.value = {
    payment_method: '',
    transaction_id: '',
    reference: '',
    notes: '',
  }

  showRenewalPayment.value = true
}


function closeRenewalPayment() {
  if (renewalSubmitting.value) {
    return
  }

  showRenewalPayment.value = false

  renewalError.value = ''
  renewalSuccess.value = ''

  renewalForm.value = {
    payment_method: '',
    transaction_id: '',
    reference: '',
    notes: '',
  }
}


/*
|--------------------------------------------------------------------------
| Complete Renewal Payment
|--------------------------------------------------------------------------
*/

async function handleRenewalPayment() {
  renewalError.value = ''
  renewalSuccess.value = ''

  if (!restaurant.value?.id) {
    renewalError.value =
      'Restaurant information is missing.'
    return
  }

  if (!subscription.value?.id) {
    renewalError.value =
      'Subscription information is missing.'
    return
  }

  const paymentMethod =
    String(
      renewalForm.value.payment_method || '',
    ).trim()

  if (!paymentMethod) {
    renewalError.value =
      'Please select a payment method.'
    return
  }

  if (
    paymentMethodRequiresTransaction(
      paymentMethod,
    ) &&
    !renewalForm.value.transaction_id.trim()
  ) {
    renewalError.value =
      'Transaction ID is required for bKash or Nagad payments.'
    return
  }

  if (
    subscription.value.status === 'active' &&
    subscription.value.end_at &&
    new Date(subscription.value.end_at).getTime() >
      Date.now()
  ) {
    renewalError.value =
      'This subscription is still active and does not require renewal.'
    return
  }

  renewalSubmitting.value = true

  try {
    if (paymentMethod === 'stripe') {
      const checkout = await createStripeCheckout({
        payment_type: 'renewal',
        restaurant_id: Number(restaurant.value.id),
        subscription_id: Number(subscription.value.id),
      })
      window.location.assign(checkout.data.checkout_url)
      return
    }

    const payload = {
      subscription_id:
        Number(subscription.value.id),

      payment_method:
        paymentMethod,

      transaction_id:
        renewalForm.value.transaction_id.trim() ||
        null,

      reference:
        renewalForm.value.reference.trim() ||
        null,

      notes:
        renewalForm.value.notes.trim() ||
        null,
    }

    const response =
      await completeSubscriptionRenewal(
        restaurant.value.id,
        payload,
      )

    if (!response?.success) {
      throw new Error(
        response?.message ||
        'Subscription renewal failed.',
      )
    }

    renewalSuccess.value =
      response.message ||
      'Subscription renewed successfully.'

    /*
     * Immediately update local subscription when
     * backend returns the renewed record.
     */
    if (response.data?.subscription) {
      subscription.value =
        response.data.subscription

      selectedSubscriptionStatus.value =
        subscription.value.status

      if (restaurant.value) {
        restaurant.value = {
          ...restaurant.value,

          subscription:
            response.data.subscription,

          status:
            response.data.restaurant?.status ??
            restaurant.value.status,
        }
      }
    }

    /*
     * Refresh authoritative data.
     */
    await loadRestaurant()
    await loadSubscription()
    await loadPayments()

    /*
     * Close after successful refresh.
     */
    window.setTimeout(() => {
      if (!renewalSubmitting.value) {
        closeRenewalPayment()
      }
    }, 700)

  } catch (error) {
    renewalError.value =
      error?.response?.data?.message ||
      error?.message ||
      'Unable to complete subscription renewal.'
  } finally {
    renewalSubmitting.value = false
  }
}


/*
|--------------------------------------------------------------------------
| Tenant Admin Credentials
|--------------------------------------------------------------------------
*/

async function handleResetAdminCredentials() {
  credentialsMessage.value = ''
  credentialsError.value = ''

  if (!restaurant.value?.id) {
    credentialsError.value =
      'Restaurant could not be identified.'
    return
  }

  if (!window.confirm(
    'Generate a new temporary admin password for this restaurant?',
  )) {
    return
  }

  credentialsLoading.value = true

  try {
    const response =
      await resetTenantAdminCredentials(
        restaurant.value.id,
      )

    if (!response?.success) {
      throw new Error(
        response?.message ||
        'Failed to reset admin credentials.',
      )
    }

    adminCredentials.value =
      response.data?.admin ?? null

    credentialsMessage.value =
      response.message ||
      'New temporary admin credentials generated successfully.'

  } catch (error) {
    credentialsError.value =
      error?.response?.data?.message ||
      error?.message ||
      'Failed to reset admin credentials.'
  } finally {
    credentialsLoading.value = false
  }
}


async function copyCredential(value) {
  if (!value) {
    return
  }

  credentialsMessage.value = ''
  credentialsError.value = ''

  try {
    await navigator.clipboard.writeText(value)

    credentialsMessage.value =
      'Credential copied to clipboard.'

  } catch {
    credentialsError.value =
      'Could not copy credential to clipboard.'
  }
}


/*
|--------------------------------------------------------------------------
| Client Access Copy Helpers
|--------------------------------------------------------------------------
*/

async function copyTextToClipboard(
  value
) {

  const text =
    String(
      value || ''
    )

  if (
    !text
  ) {
    return false
  }


  try {

    if (
      navigator?.clipboard?.writeText
    ) {

      await navigator.clipboard.writeText(
        text
      )

      return true
    }

  } catch {
    // Continue to fallback.
  }


  try {

    const textarea =
      document.createElement(
        'textarea'
      )

    textarea.value =
      text

    textarea.setAttribute(
      'readonly',
      ''
    )

    textarea.style.position =
      'fixed'

    textarea.style.opacity =
      '0'

    document.body.appendChild(
      textarea
    )

    textarea.select()

    const copied =
      document.execCommand(
        'copy'
      )

    document.body.removeChild(
      textarea
    )

    return copied

  } catch {

    return false
  }
}


async function copyClientAccessUrl() {

  const copied =
    await copyTextToClipboard(
      getTenantUrl()
    )


  if (
    !copied
  ) {
    credentialsError.value =
      'Could not copy the client access URL.'

    return
  }


  credentialsError.value =
    ''

  clientAccessCopied.value =
    true


  if (
    clientAccessCopyTimer
  ) {

    window.clearTimeout(
      clientAccessCopyTimer
    )
  }


  clientAccessCopyTimer =
    window.setTimeout(
      () => {

        clientAccessCopied.value =
          false
      },
      1800
    )
}


async function copyTenantKey() {

  const copied =
    await copyTextToClipboard(
      restaurant.value?.tenant_key
    )


  if (
    !copied
  ) {
    credentialsError.value =
      'Could not copy the tenant key.'

    return
  }


  credentialsError.value =
    ''

  tenantKeyCopied.value =
    true


  if (
    tenantKeyCopyTimer
  ) {

    window.clearTimeout(
      tenantKeyCopyTimer
    )
  }


  tenantKeyCopyTimer =
    window.setTimeout(
      () => {

        tenantKeyCopied.value =
          false
      },
      1800
    )
}


/*
|--------------------------------------------------------------------------
| Full Client Handover
|--------------------------------------------------------------------------
*/

async function copyFullHandover() {

  handoverMessage.value =
    ''

  handoverError.value =
    ''


  if (
    !hasHandoverCredentials.value
  ) {

    handoverError.value =
      'Generate tenant admin credentials before copying the full handover.'

    return
  }


  const copied =
    await copyTextToClipboard(
      handoverText.value
    )


  if (
    !copied
  ) {

    handoverError.value =
      'Could not copy the client handover details.'

    return
  }


  handoverCopied.value =
    true

  handoverMessage.value =
    'Client handover details copied to clipboard.'


  if (
    handoverCopyTimer
  ) {

    window.clearTimeout(
      handoverCopyTimer
    )
  }


  handoverCopyTimer =
    window.setTimeout(
      () => {

        handoverCopied.value =
          false
      },
      1800
    )
}


async function shareHandover() {

  handoverMessage.value =
    ''

  handoverError.value =
    ''


  if (
    !hasHandoverCredentials.value
  ) {

    handoverError.value =
      'Generate tenant admin credentials before sharing the handover.'

    return
  }


  if (
    !canShareHandover.value
  ) {

    await copyFullHandover()

    return
  }


  handoverSharing.value =
    true


  try {

    await navigator.share({
      title:
        `${restaurant.value?.name || 'Restaurant'} RMS Access`,

      text:
        handoverText.value,
    })


    handoverMessage.value =
      'Client handover shared successfully.'

  } catch (
    error
  ) {

    if (
      error?.name !== 'AbortError'
    ) {

      handoverError.value =
        'Unable to share the handover details.'
    }

  } finally {

    handoverSharing.value =
      false
  }
}


/*
|--------------------------------------------------------------------------
| Tenant URL
|--------------------------------------------------------------------------
*/

function getTenantUrl() {

  const tenantKey =
    String(
      restaurant.value?.tenant_key || '',
    ).trim()


  if (!tenantKey) {
    return '#'
  }


  const protocol =
    window.location.protocol


  const host =
    window.location.hostname


  const port =
    window.location.port
      ? `:${window.location.port}`
      : ''


  return (
    `${protocol}//` +
    `${host}` +
    `${port}` +
    `/tenant/` +
    `${encodeURIComponent(tenantKey)}`
  )
}


/*
|--------------------------------------------------------------------------
| Format Price
|--------------------------------------------------------------------------
*/

function formatPrice(price) {
  const amount = Number(price ?? 0)

  return amount.toLocaleString(
    'en-BD',
    {
      minimumFractionDigits: 0,
      maximumFractionDigits: 2,
    },
  )
}


/*
|--------------------------------------------------------------------------
| Billing Label
|--------------------------------------------------------------------------
*/

function billingLabel(plan) {
  const count = Number(
    plan?.billing_interval_count || 1,
  )

  const interval =
    plan?.billing_interval || 'month'

  if (count === 1) {
    return interval
  }

  return `${count} ${interval}s`
}


/*
|--------------------------------------------------------------------------
| Date Formatting
|--------------------------------------------------------------------------
*/

function formatDateTime(value) {
  if (!value) {
    return '—'
  }

  const date = new Date(value)

  if (Number.isNaN(date.getTime())) {
    return value
  }

  return date.toLocaleString(
    'en-GB',
    {
      day: '2-digit',
      month: 'short',
      year: 'numeric',
      hour: '2-digit',
      minute: '2-digit',
    },
  )
}


/*
|--------------------------------------------------------------------------
| Remaining Subscription
|--------------------------------------------------------------------------
*/

function remainingSubscriptionText(endAt) {
  if (!endAt) {
    return 'N/A'
  }

  const end = new Date(endAt)
  const now = new Date()

  if (Number.isNaN(end.getTime())) {
    return 'N/A'
  }

  const diffMs =
    end.getTime() - now.getTime()

  if (diffMs <= 0) {
    return 'Expired'
  }

  const totalMinutes =
    Math.floor(
      diffMs / (1000 * 60),
    )

  const days =
    Math.floor(
      totalMinutes / (60 * 24),
    )

  const hours =
    Math.floor(
      (totalMinutes % (60 * 24)) / 60,
    )

  if (days > 0) {
    return (
      `${days} day` +
      `${days > 1 ? 's' : ''} remaining`
    )
  }

  if (hours > 0) {
    return (
      `${hours} hour` +
      `${hours > 1 ? 's' : ''} remaining`
    )
  }

  return 'Less than 1 hour remaining'
}


/*
|--------------------------------------------------------------------------
| Load Everything
|--------------------------------------------------------------------------
*/

async function loadAll() {
  loading.value = true
  error.value = ''

  try {
    await Promise.all([
      loadRestaurant(),
      loadSubscription(),
      loadPayments(),
      loadPaymentMethods(),
    ])
  } catch (exception) {
    error.value =
      exception?.response?.data?.message ||
      exception?.message ||
      'Failed to load restaurant details.'
  } finally {
    loading.value = false
  }
}


/*
|--------------------------------------------------------------------------
| Live Lifecycle Refresh
|--------------------------------------------------------------------------
|
| 1. Local clock hides tenant access immediately when end_at is reached.
| 2. Every 10 seconds the latest restaurant/subscription state is fetched.
| 3. When the local clock detects expiry, backend synchronization is triggered
|    immediately instead of waiting for the next polling interval.
|
*/

const LIFECYCLE_REFRESH_MS = 10_000

let lifecycleClockTimer = null
let lifecycleRefreshTimer = null
let expirySyncInProgress = false


async function refreshLifecycleState() {
  if (
    loading.value ||
    paymentSubmitting.value ||
    renewalSubmitting.value ||
    upgradeSubmitting.value ||
    planUpdateSubmitting.value
  ) {
    return
  }

  if (
    typeof document !== 'undefined' &&
    document.visibilityState === 'hidden'
  ) {
    return
  }

  try {
    await Promise.all([
      loadRestaurant(),
      loadSubscription(),
    ])
  } catch {
    /*
     * Keep the last successfully loaded state during a silent background
     * refresh. Manual Refresh still reports errors through loadAll().
     */
  }
}


async function synchronizeExpiryIfNeeded() {
  if (expirySyncInProgress) {
    return
  }

  if (
    subscription.value?.status !== 'active' ||
    !subscriptionHasEnded.value
  ) {
    return
  }

  expirySyncInProgress = true

  try {
    await refreshLifecycleState()
  } finally {
    expirySyncInProgress = false
  }
}


function tickLifecycleClock() {
  currentTime.value = Date.now()

  synchronizeExpiryIfNeeded()
}


function startLifecycleWatchers() {
  stopLifecycleWatchers()

  lifecycleClockTimer = window.setInterval(
    tickLifecycleClock,
    1_000,
  )

  lifecycleRefreshTimer = window.setInterval(
    refreshLifecycleState,
    LIFECYCLE_REFRESH_MS,
  )
}


function stopLifecycleWatchers() {
  if (lifecycleClockTimer !== null) {
    window.clearInterval(
      lifecycleClockTimer,
    )

    lifecycleClockTimer = null
  }

  if (lifecycleRefreshTimer !== null) {
    window.clearInterval(
      lifecycleRefreshTimer,
    )

    lifecycleRefreshTimer = null
  }
}


/*
|--------------------------------------------------------------------------
| Initial Load
|--------------------------------------------------------------------------
*/

onMounted(async () => {
  await loadAll()

  if (route.query.stripe_payment === 'success' && route.query.session_id) {
    try {
      const result = await confirmStripePayment(String(route.query.session_id))
      const admin = result?.data?.admin
      if (admin) adminCredentials.value = admin
      stripeReturnMessage.value = admin
        ? 'Stripe payment confirmed. Restaurant activated; the generated tenant admin credentials are available below.'
        : 'Stripe payment confirmed and subscription updated.'
      await loadAll()
    } catch (stripeError) {
      stripeReturnError.value = stripeError?.message || 'Payment confirmation is still pending. Refresh this page shortly.'
    } finally {
      window.history.replaceState({}, '', window.location.pathname)
    }
  } else if (route.query.stripe_payment === 'cancelled') {
    stripeReturnError.value = 'Stripe Checkout was cancelled. The payment remains unpaid.'
    window.history.replaceState({}, '', window.location.pathname)
  }

  currentTime.value = Date.now()

  startLifecycleWatchers()
})


onBeforeUnmount(() => {
  stopLifecycleWatchers()

  if (
    clientAccessCopyTimer
  ) {
    window.clearTimeout(
      clientAccessCopyTimer
    )
  }

  if (
    tenantKeyCopyTimer
  ) {
    window.clearTimeout(
      tenantKeyCopyTimer
    )
  }

  if (
    handoverCopyTimer
  ) {
    window.clearTimeout(
      handoverCopyTimer
    )
  }
})

</script>
