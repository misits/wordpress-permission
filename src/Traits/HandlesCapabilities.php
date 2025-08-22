<?php
/**
 * Handles Capabilities Trait
 *
 * Provides capability management functionality
 *
 * @package WordPressPermission
 */

namespace WordPressPermission\Traits;

defined('ABSPATH') or exit;

trait HandlesCapabilities {
    
    /**
     * Create a new capability
     */
    public function createCapability($capability, $description = '') {
        return $this->getCapabilityManager()->create($capability, $description);
    }
    
    /**
     * Check if capability exists
     */
    public function capabilityExists($capability) {
        return $this->getCapabilityManager()->exists($capability);
    }
    
    /**
     * Get all custom capabilities
     */
    public function getCustomCapabilities() {
        return $this->getCapabilityManager()->getCustomCapabilities();
    }
    
    /**
     * Delete a custom capability
     */
    public function deleteCapability($capability) {
        return $this->getCapabilityManager()->delete($capability);
    }
    
    /**
     * Get capability groups
     */
    public function getCapabilityGroups() {
        return $this->getCapabilityManager()->getGroups();
    }
}