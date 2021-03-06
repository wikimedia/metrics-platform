package org.wikimedia.metrics_platform.curation.rules;

import com.google.gson.annotations.SerializedName;

import java.util.Collection;

public class CollectionCurationRules<T> {
    private T contains;
    @SerializedName("does_not_contain") private T doesNotContain;
    @SerializedName("contains_all") private Collection<T> containsAll;
    @SerializedName("contains_any") private Collection<T> containsAny;

    public CollectionCurationRules(Builder<T> builder) {
        this.contains = builder.contains;
        this.doesNotContain = builder.doesNotContain;
        this.containsAll = builder.containsAll;
        this.containsAny = builder.containsAny;
    }

    public boolean apply(Collection<T> value) {
        if (contains != null && !value.contains(contains)) {
            return false;
        }
        if (doesNotContain != null && value.contains(doesNotContain)) {
            return false;
        }
        if (containsAll != null && !value.containsAll(containsAll)) {
            return false;
        }
        if (containsAny != null) {
            boolean found = false;
            for (T el : containsAny) {
                if (value.contains(el)) {
                    found = true;
                    break;
                }
            }
            if (!found) {
                return false;
            }
        }
        return true;
    }

    public static class Builder<T> {
        private T contains;
        private T doesNotContain;
        private Collection<T> containsAll;
        private Collection<T> containsAny;

        public Builder<T> contains(T contains) {
            this.contains = contains;
            return this;
        }

        public Builder<T> doesNotContain(T doesNotContain) {
            this.doesNotContain = doesNotContain;
            return this;
        }

        public Builder<T> containsAll(Collection<T> containsAll) {
            this.containsAll = containsAll;
            return this;
        }

        public Builder<T> containsAny(Collection<T> containsAny) {
            this.containsAny = containsAny;
            return this;
        }

        public CollectionCurationRules<T> build() {
            return new CollectionCurationRules<>(this);
        }
    }
}
